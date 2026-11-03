# Contrat web

Application Blade avec sessions, messages flash et formulaires protégés. Aucune API REST
publique ni application frontend séparée n’est introduite.

## Routes

| Méthode | Chemin | Nom | Résultat |
|---|---|---|---|
| GET | / | home | Redirection vers tickets.index |
| GET | /tickets | tickets.index | Liste filtrée et paginée, 200 |
| GET | /tickets/create | tickets.create | Formulaire, 200 |
| POST | /tickets | tickets.store | Création, puis 303 vers tickets.show |
| GET | /tickets/{ticket} | tickets.show | Détail, 200 ou 404 |

Déclarer `/tickets/create` avant la route de détail. Le paramètre ticket est un entier
positif ; le service/repository effectue la recherche, sans accès direct au modèle dans
le contrôleur ou via un binding qui contournerait cette chaîne.

## Entrées

POST : `title`, `description`, `priority`, `status` et jeton CSRF.
Après trim : titre 1–160 caractères, description 1–5 000 ; enums définis dans
[data-model.md](../data-model.md). L’utilisateur n’envoie aucun résultat d’analyse.

GET liste : status et priority facultatifs, vide signifie tous ; page entier ≥1.
Une valeur invalide produit une page d’erreur 422 avec retour vers la liste sans filtres.
Une page valide mais au-delà des résultats reste un état sans résultat avec retour à la
première page. Les liens de pagination ne conservent que les paramètres validés.

## Échecs

| Cas | Comportement |
|---|---|
| Formulaire invalide | Redirection 302 vers create, erreurs et ancienne saisie, aucun appel LLM |
| CSRF invalide | 419, message français et lien vers un formulaire neuf |
| Limite atteinte | 429 et délai de reprise ; aucun appel fournisseur |
| Ticket absent | 404 sobre avec lien vers la liste |
| Fournisseur absent ou défaillant | Secours local ; création normale si la base fonctionne |
| Échec de persistance | Retour create avec erreur et ancienne saisie ; aucun message de succès |

Un échec de persistance est capturé uniquement autour de l’écriture ; ne pas masquer les
erreurs de développement derrière un succès. Préserver la saisie via la session existante,
sans journaliser description ou bindings d’exception.

## Panneaux et JavaScript

Les GET create/show peuvent accepter `panel=1` pour retourner uniquement leur contenu
HTML partagé. Sans cette option, retourner une page complète. Les liens HTML pointent
toujours vers les pages canoniques et le script ajoute l’option lors de l’ouverture du panneau.
Une erreur de chargement laisse le lien normal utilisable.

Les formulaires POST conservent une soumission normale : après succès ou validation échouée,
la navigation serveur présente le détail ou le formulaire complet. Cette première version
évite une deuxième gestion de validation côté client. L’état d’envoi bloque uniquement les
soumissions simultanées ; il ne fournit pas d’idempotence globale.
