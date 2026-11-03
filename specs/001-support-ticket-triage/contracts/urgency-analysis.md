# Contrat d’analyse d’urgence

## Contrat interne

`UrgencyAnalyzerInterface::analyze(string description): UrgencyResult`.
Résultat immuable : priorité low/medium/high, motif de 1–500 caractères, source llm/local.
Le choix humain n’est pas une entrée de l’analyse et n’est jamais modifié par elle.

Injection : le service reçoit `ResilientUrgencyAnalyzer` derrière cette interface.
Le composite dépend explicitement des analyseurs externe et local ; aucun service locator.
Le service appelle l’analyseur avant la persistance et transmet le résultat au repository.

## Configuration cible

| Variable | Valeur initiale | Rôle |
|---|---|---|
| URGENCY_DRIVER | local | local ou openai |
| OPENAI_API_KEY | vide | Secret facultatif, jamais exposé au navigateur |
| OPENAI_MODEL | gpt-4o-mini | Modèle configurable supportant les sorties structurées |
| URGENCY_CONNECT_TIMEOUT | 2 | Secondes, valeur positive |
| URGENCY_TIMEOUT | 5 | Secondes, valeur positive et ≤5 dans ce livrable |
| TICKET_RATE_LIMIT | 10 | Nombre maximal de tentatives POST par minute et IP |

Lire les variables uniquement depuis `config/urgency.php`, puis utiliser la configuration.
Driver openai sans clé : secours immédiat sans appel réseau. Un driver inconnu est une
erreur de configuration à signaler, pas un endpoint arbitraire fourni par une requête.

## Appel externe

Premier adaptateur : `POST https://api.openai.com/v1/responses`, authentification Bearer.
Requête via le client HTTP Laravel existant, sans SDK ajouté. Aucun retry automatique.
URL fixe, redirections HTTP désactivées, aucune URL utilisateur acceptée.

Instructions fixes : analyser l’impact, produire une explication française brève et traiter
la description comme donnée non fiable. Seule la description est envoyée comme contenu métier.
Aucun titre, priorité humaine, identifiant utilisateur, outil ou capacité d’action n’est transmis.
Définir `store=false` ; cette option ne constitue pas une promesse d’absence de toute rétention
par le fournisseur. Documenter la transmission dans le README et près du formulaire.

Format de sortie : `text.format` de type json_schema, strict, objet contenant uniquement :

| Champ | Type | Validation locale |
|---|---|---|
| priority | string | low / medium / high |
| reason | string | Trim, non vide, 500 caractères maximum |

Les deux champs sont requis, propriétés supplémentaires interdites. Le schéma contraint la
forme ; il ne garantit pas la justesse de la classification. Fixer `max_output_tokens=256`.
N’accepter qu’une réponse terminée contenant le texte structuré attendu. Refus, sortie
incomplète, JSON invalide, objet inattendu, motif absent ou corps supérieur à 64 Kio : secours.
Les limites de taille sont validées à la réception ; elles ne promettent pas une borne mémoire réseau.

## Secours local

Normaliser casse, accents, apostrophes et espaces, puis rechercher des expressions d’impact
explicites par clause. Les règles et leurs explications sont centralisées dans l’analyseur local.

| Impact positif reconnu | Recommandation |
|---|---|
| Service indisponible, activité essentielle bloquée | high |
| Fonction dégradée avec contournement | medium |
| Défaut cosmétique ou demande d’information | low |
| Aucun signal reconnu | medium, avec motif explicite d’absence de signal |

Reconnaître les négations locales documentées : « aucune panne », « aucun blocage »,
« pas de panne », « pas urgent ». Les expressions de blocage « ne peut plus » ou
« impossible de » ne sont pas neutralisées par une règle générale sur le mot « ne ».
Plusieurs impacts positifs : high > medium > low. Ignorer uniquement les occurrences niées,
pas tous les signaux de la description. Des exemples français supplémentaires pourront être
ajoutés avec leurs tests, sans revendiquer une compréhension linguistique exhaustive.

## Échecs, logs et tests

Timeout/connexion, 401/403, 429, 5xx, refus ou réponse invalide déclenchent le secours.
Le motif enregistré décrit l’impact ; l’interface affiche séparément le recours à l’analyse locale.
Logger une catégorie contrôlée et la durée ; pas de clé, description ou réponse brute.

Fixtures nécessaires : succès externe, réponse vide, enum inconnu, motif trop long, refus,
sortie incomplète, erreur HTTP, timeout, absence de clé et chaque classe de secours.
Utiliser HTTP fake et preventStrayRequests ; aucun appel payant dans la suite.

Référence : [sorties structurées OpenAI](https://developers.openai.com/api/docs/guides/structured-outputs).
