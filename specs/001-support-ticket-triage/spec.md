# Feature Specification: Fast Track Support, projet complet

**Feature Branch**: Non créée ; aucun hook de branche configuré.

**Created**: 2026-11-03

**Status**: Draft

**Input**: Réaliser le mini-projet complet de gestion de tickets : création,
consultation, filtres, recommandation d’urgence par analyse externe avec secours
local, sécurité, interface française accessible, vérifications et livraison reproductible.

Références : [cahier des charges](../../docs/cahier-des-charges.md),
[constitution](../../.specify/memory/constitution.md),
[architecture](../../docs/architecture-docs.md) et
[prototype Figma](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3).
Ce document couvre l’ensemble du premier livrable demandé par le recruteur.
Il définit les résultats attendus ; le plan et les tâches seront préparés séparément.

## Clarifications

### Session 2026-11-03

- Q: Quelle règle métier détermine l’urgence du secours local ? → A: Classer selon
  l’impact : Haute pour un service indisponible ou une activité essentielle bloquée ;
  Moyenne pour une fonction dégradée avec contournement ; Basse pour un problème
  cosmétique ou une demande d’information. Sans signal reconnu, proposer Moyenne.
  Si plusieurs signaux positifs correspondent, retenir la plus haute urgence ;
  les signaux niés ne comptent pas.

## User Scenarios & Testing *(mandatory)*

Conformément à la constitution V, des tests Pest pertinents sont explicitement
requis, ainsi qu’un parcours navigateur et une QA manuelle desktop, mobile et clavier.
Les appels au fournisseur doivent être simulés dans les tests automatisés pour
vérifier succès et pannes sans clé réelle ni consommation de service payant.
Les vérifications ci-dessous décrivent des contrôles à exécuter, pas des résultats acquis.

### User Story 1 - Enregistrer une demande (Priority: P1)

En tant que personne chargée du support, je saisis une demande afin de la conserver
et de la retrouver avec sa priorité et son statut.

**Why this priority**: La création persistante constitue la fonction centrale du produit.

**Independent Test**: Depuis une installation vide, créer une demande puis la consulter
et rafraîchir la page. L’analyse locale permet ce parcours sans service externe.

**Acceptance Scenarios**:

1. **Given** un formulaire vide, **When** je l’ouvre, **Then** les champs titre,
   description, priorité et statut sont identifiés ; Moyenne et Ouvert sont présélectionnés.
2. **Given** des valeurs valides, **When** je confirme, **Then** un seul ticket est
   enregistré, une confirmation apparaît et je peux consulter sa référence et son détail.
3. **Given** un champ vide, trop long ou une valeur non autorisée, **When** je confirme,
   **Then** la demande est refusée, aucun ticket n’est créé, le champ est expliqué
   et les valeurs déjà saisies sont conservées.
4. **Given** une création en cours, **When** je tente de confirmer à nouveau,
   **Then** l’interface empêche un second envoi tant que le premier n’est pas terminé.
5. **Given** une impossibilité de conserver la demande, **When** la création échoue,
   **Then** aucun succès n’est annoncé ; une erreur compréhensible permet de réessayer.
6. **Given** un ticket enregistré, **When** je recharge ou redémarre l’application,
   **Then** ses informations restent disponibles.

### User Story 2 - Consulter et trier la file (Priority: P1)

En tant que personne chargée du support, je consulte et filtre les tickets afin
de repérer les demandes pertinentes.

**Why this priority**: Les demandes enregistrées doivent être retrouvables sans ambiguïté.

**Independent Test**: Avec des tickets de démonstration couvrant chaque priorité et
statut, vérifier les résultats sans créer de nouvelle demande.

**Acceptance Scenarios**:

1. **Given** plusieurs tickets, **When** j’ouvre la file, **Then** je vois référence,
   titre, aperçu de description, priorité, statut et date, du plus récent au plus ancien.
2. **Given** un statut ou une priorité sélectionné, **When** le filtre est appliqué,
   **Then** seuls les tickets correspondants sont présentés et leur nombre est indiqué.
3. **Given** les deux filtres sélectionnés, **When** ils sont appliqués,
   **Then** chaque résultat respecte simultanément le statut et la priorité.
4. **Given** des filtres actifs, **When** je les réinitialise, **Then** la file complète
   redevient accessible ; une navigation entre pages conserve les filtres actifs.
5. **Given** aucun ticket ou aucun résultat, **When** j’ouvre la file,
   **Then** un état vide distinct explique comment créer une demande ou retirer les filtres.
6. **Given** une référence visible, **When** je l’ouvre au clavier ou à la souris,
   **Then** le détail complet présente les données du bon ticket.
7. **Given** un filtre inconnu ou une référence inexistante, **When** je demande
   cette vue, **Then** une erreur compréhensible est affichée sans détails techniques.

### User Story 3 - Comprendre l’urgence, même en cas de panne (Priority: P1)

En tant que personne chargée du support, je consulte une recommandation expliquée
sans perdre la priorité que j’ai choisie.

**Why this priority**: L’analyse d’urgence est le différenciateur demandé par le test.

**Independent Test**: Créer des demandes avec réponses externes contrôlées, puis avec
service indisponible, et comparer priorité choisie, recommandation, explication et source.

**Acceptance Scenarios**:

1. **Given** une description et un service externe disponible, **When** la création
   aboutit avec une réponse exploitable, **Then** une recommandation Basse, Moyenne
   ou Haute, une explication et la source externe sont affichées et conservées.
2. **Given** une panne, une limitation du fournisseur, un délai dépassé ou une réponse
   inexploitable, **When** une demande valide est créée, **Then** une analyse locale
   fournit le résultat et la création peut aboutir si la persistance fonctionne.
3. **Given** aucune configuration d’accès externe, **When** je crée une demande,
   **Then** l’analyse locale fonctionne sans accès à un fournisseur.
4. **Given** un résultat de secours, **When** j’ouvre le ticket, **Then** la source
   locale et un message expliquant le recours au secours sont visibles.
5. **Given** une priorité choisie différente de la recommandation, **When** le ticket
   est enregistré ou consulté, **Then** les deux valeurs restent distinctes
   et la priorité humaine n’est jamais remplacée automatiquement.
6. **Given** une description sans signal reconnu, **When** l’analyse locale s’exécute,
   **Then** elle propose Moyenne et indique qu’aucun signal déterminant n’a été reconnu.
7. **Given** « pas urgent, simple décalage visuel, tout reste utilisable », **When**
   l’analyse locale s’exécute, **Then** elle propose Basse ; le mot « urgent » seul
   ne provoque pas Haute.
8. **Given** un service indisponible ou une activité essentielle bloquée, **When**
   l’analyse locale s’exécute, **Then** elle propose Haute et explique cet impact.
9. **Given** une fonction dégradée avec une solution de contournement, **When**
   l’analyse locale s’exécute, **Then** elle propose Moyenne et explique cet impact.
10. **Given** un problème uniquement cosmétique ou une demande d’information,
    **When** l’analyse locale s’exécute, **Then** elle propose Basse.
11. **Given** plusieurs signaux positifs correspondant à des urgences différentes,
    **When** l’analyse locale s’exécute, **Then** elle retient la plus haute urgence
    reconnue ; un service indisponible reste Haute même si un défaut visuel est aussi cité.
12. **Given** « aucune panne, aucun blocage, simple demande d’information », **When**
    l’analyse locale s’exécute, **Then** elle propose Basse ; les signaux niés
    « panne » et « blocage » ne sont pas retenus.

### User Story 4 - Évaluer et installer le livrable (Priority: P2)

En tant que destinataire du test, je découvre le prototype, installe le projet
et reproduis les principaux parcours avec des données fictives.

**Why this priority**: Une application non installable ne permet pas d’évaluer le travail.
Cette priorité organise la réalisation ; cette livraison reste obligatoire.

**Independent Test**: Suivre le README depuis un environnement propre avec les
prérequis installés, sans clé d’analyse externe et sans base préexistante.

**Acceptance Scenarios**:

1. **Given** le README, **When** je commence la revue, **Then** Figma est le premier
   lien de présentation et les simulations sont distinguées des fonctions réelles.
2. **Given** les prérequis disponibles, **When** je suis les instructions,
   **Then** je peux initialiser les données, charger les exemples et lancer le projet.
3. **Given** aucune clé externe, **When** je crée et filtre des tickets,
   **Then** les parcours principaux et l’analyse locale fonctionnent.
4. **Given** les commandes de vérification documentées, **When** je les exécute,
   **Then** je peux reproduire les tests sans clé externe ; les contrôles manuels
   et limites connues sont clairement indiqués.

### Edge Cases

- Espaces seuls : refus après suppression des espaces de début et de fin.
- Titre : accepter 1 à 160 caractères ; refuser au-delà. Description : 1 à 5 000.
- Priorité ou statut non autorisé : refus sans création.
- Aucun résultat filtré : proposer la réinitialisation ; conserver les filtres lors de la pagination.
- Dates identiques : un ordre secondaire stable empêche les permutations arbitraires.
- Texte ressemblant à du code ou à des instructions pour le modèle : ne pas l’exécuter ;
  une réponse d’analyse hors du format autorisé déclenche le secours.
- Réponse externe vide, mal formée, priorité inconnue ou explication absente : secours local.
- Demandes répétées au-delà de la limite configurée : refus compréhensible, sans appel externe.
- Création sans protection de formulaire valide : refus, sans ticket créé ni appel externe.
- Fermeture d’un panneau : retour du focus au déclencheur ; navigation mobile sans défilement horizontal.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Le système DOIT proposer un formulaire avec titre, description,
  priorité et statut. Valeurs par défaut : Moyenne et Ouvert. Référence : F01–F03.
- **FR-002**: Le système DOIT valider les champs côté serveur et appliquer les
  limites et valeurs définies dans Edge Cases avant toute analyse. Référence : F01, S01.
- **FR-003**: Une création réussie DOIT conserver le ticket, attribuer une référence
  unique, confirmer le succès et rendre son détail accessible. Référence : F01.
- **FR-004**: Une erreur de validation ou d’enregistrement DOIT être expliquée sans
  faux message de succès ; les données du formulaire DOIVENT être récupérables. Référence : F01.
- **FR-005**: L’interface DOIT empêcher les doubles soumissions pendant un envoi en cours.
  Ce contrôle ne promet pas la déduplication de demandes créées séparément. Référence : F01.
- **FR-006**: La file DOIT afficher référence, titre, aperçu, priorité, statut et date,
  avec pagination, nombre de résultats et ordre stable du plus récent au plus ancien. Référence : F04.
- **FR-007**: Le système DOIT proposer les filtres statut et priorité, combinables,
  conservés lors de la pagination et réinitialisables. Référence : F04.
- **FR-008**: Les états vide, sans résultat, filtre invalide et ticket introuvable
  DOIVENT produire une réponse compréhensible. Référence : F04.
- **FR-009**: Chaque ticket créé DOIT disposer d’une urgence recommandée,
  d’une explication et d’une source consultables après rechargement. Référence : F05.
- **FR-010**: Le système DOIT tenter l’analyse externe si elle est configurée,
  avec une attente bornée ; une réponse inexploitable DOIT être refusée. Référence : F05.
- **FR-011**: Toute indisponibilité externe, délai dépassé ou absence de configuration
  DOIT déclencher le secours local sans empêcher la création lorsque le stockage fonctionne.
  Référence : F05, constitution IV.
- **FR-012**: Le système DOIT distinguer priorité humaine et recommandation,
  préserver la première et expliquer la provenance du résultat. Référence : décision du cahier des charges.
- **FR-013**: Le secours local DOIT classer l’urgence selon l’impact reconnu :
  Haute pour un service indisponible ou une activité essentielle bloquée ; Moyenne
  pour une fonction dégradée avec contournement ; Basse pour un problème cosmétique
  ou une demande d’information. Il DOIT ignorer les signaux niés, retenir la plus
  haute urgence parmi les signaux positifs et proposer Moyenne sans signal reconnu.
  Le résultat DOIT être reproductible pour un même texte et les mêmes règles.
  Référence : F05, constitution IV ; clarification du 2026-11-03.
- **FR-014**: L’interface DOIT être en français, utilisable au clavier et à 390px,
  avec labels explicites, focus visible, gestion du focus des panneaux et signification
  des badges compréhensible sans couleur. Référence : constitution, contraintes techniques.
- **FR-015**: Les données saisies et les explications DOIVENT être affichées comme
  texte sans exécuter de contenu actif. Référence : S01.
- **FR-016**: Les créations non autorisées par la protection de formulaire et les
  demandes au-delà d’une limite configurable DOIVENT être refusées avant analyse.
  Référence : constitution III.
- **FR-017**: Les secrets et détails d’erreurs internes NE DOIVENT PAS apparaître
  dans l’interface, le livrable ou les journaux destinés à la revue. Les descriptions
  complètes et réponses brutes sensibles NE DOIVENT PAS être journalisées. Référence : constitution III.
- **FR-018**: Le système DOIT transmettre uniquement la description nécessaire à
  l’analyse externe ; ce traitement DOIT être documenté. Référence : architecture, sécurité.
- **FR-019**: La remise DOIT comprendre sources, instructions d’installation,
  initialisation des données, exemples fictifs, configuration sans secret,
  commandes de vérification et limites connues. Référence : L01.
- **FR-020**: Les fonctions principales DOIVENT être démontrables sans configuration
  externe et le README DOIT présenter Figma avant les autres liens. Référence : L01, décision utilisateur.
- **FR-021**: Les contrôles automatisés, navigateur et manuels définis ici DOIVENT
  être exécutés avant remise ; leurs résultats réels DOIVENT être consignés.
  Référence : constitution V.

### Key Entities *(include if feature involves data)*

- **Ticket** : référence, titre, description, priorité choisie, statut et dates.
- **Recommandation d’urgence** : priorité suggérée, explication et source externe
  ou locale, rattachées à un ticket. Cette distinction métier n’impose pas un stockage séparé.
- **Critères de consultation** : statut, priorité et position dans la liste de résultats.

## Success Criteria *(mandatory)*

### Measurable Outcomes

Ces critères sont des objectifs de validation, pas des performances déjà constatées.

- **SC-001**: Lors du parcours manuel de démonstration, l’opérateur crée puis
  consulte une demande en moins de deux minutes, avec un titre et une description préparés.
- **SC-002**: Pour les trois statuts et trois priorités de démonstration, 100 % des
  combinaisons de filtres testées ne présentent que les tickets correspondants ;
  la réinitialisation et la pagination ne perdent aucun ticket attendu.
- **SC-003**: Tous les cas de données invalides, protection de formulaire invalide
  et dépassement de limite testés créent zéro ticket et affichent un message explicatif.
- **SC-004**: Dans un environnement local fonctionnel, chaque scénario simulé de
  panne externe ou d’absence de configuration permet une création avec secours
  et confirmation en dix secondes maximum après l’envoi.
- **SC-005**: Dans tous les scénarios d’analyse testés, la priorité choisie reste
  inchangée ; recommandation, explication et source sont consultables après rechargement.
- **SC-006**: Les parcours création, filtres et consultation sont réalisables au
  clavier et à 390px sans défilement horizontal ni information essentielle inaccessible.
- **SC-007**: Avec les prérequis disponibles, une installation propre suit toutes
  les commandes documentées sans étape manquante et permet une démonstration sans clé externe.
- **SC-008**: La remise comporte un résultat consigné pour chaque contrôle requis ;
  toute limite ou vérification non exécutée est explicitement signalée.

## Assumptions

- Périmètre : démonstration locale pour le test ; aucune authentification, gestion
  d’équipe, pièce jointe, notification, statistique, suppression ou édition demandée.
- Les priorités sont Basse, Moyenne, Haute ; les statuts sont Ouvert, En cours, Résolu.
- Les limites 160/5 000 caractères et les objectifs temporels sont des valeurs de
  cadrage proposées à partir du besoin, non des chiffres fournis par le recruteur.
- L’analyse commence à la création ; la recommandation est informative et ne modifie
  pas automatiquement la priorité humaine. La consultation ne relance pas l’analyse.
- Le secours applique des règles textuelles explicites ; aucune compréhension
  linguistique exhaustive ou fiabilité clinique/financière n’est revendiquée.
- L’accès externe dépend d’une configuration fournie séparément. Le fournisseur
  et les réglages techniques seront choisis lors du plan, sans secret dans la spécification.
- Le prototype est la référence visuelle ; ses données et analyses simulées ne
  constituent pas des preuves du fonctionnement de l’application.
- L’absence de plan d’implémentation et de tâches à cette étape est volontaire.
