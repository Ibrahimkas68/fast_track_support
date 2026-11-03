# Architecture

Statut : implémentée pour le livrable local. Résultats et limites : [verification.md](verification.md).

## Choix techniques

| Élément | Choix et justification |
|---|---|
| Application | Monolithe Laravel 13 : un projet et des conventions communes |
| Interface | Blade et Tailwind : rendu serveur et composants réutilisables |
| Base | SQLite pour une installation locale simple |
| Persistance | Repository Eloquent derrière TicketRepositoryInterface |
| Analyse | Adaptateur LLM interchangeable et analyseur local indépendant |
| Vérification | Pest, puis parcours navigateur et QA manuelle |

Symfony et CodeIgniter sont autorisés, mais Laravel est déjà initialisé. Une application Vue/Nuxt séparée ajouterait une seconde application et un contrat REST sans besoin identifié dans le périmètre retenu.

## Diagrammes

Ces diagrammes Mermaid sont affichés directement par GitHub.

### Architecture du livrable

```mermaid
flowchart TB
    UI["Support : Blade et Tailwind"] --> HTTP["Routes web : CSRF, limitation, validation"]
    HTTP --> C["TicketController"]
    C --> S["TicketService"]
    S --> A["UrgencyAnalyzerInterface"]
    A --> COMPOSITE["ResilientUrgencyAnalyzer"]
    COMPOSITE -->|API configurée| EXTERNAL["OpenAiUrgencyAnalyzer : délai maximal 5 s"]
    COMPOSITE -->|Mode local ou clé absente| LOCAL["RuleBasedUrgencyAnalyzer"]
    EXTERNAL -. Échec ou réponse invalide .-> LOCAL
    S --> RI["TicketRepositoryInterface"]
    RI --> REPO["EloquentTicketRepository"]
    REPO --> MODEL["Ticket"]
    MODEL --> DB[("SQLite")]
```

### Création et secours local

```mermaid
sequenceDiagram
    autonumber
    actor Support
    participant Web as Laravel / Controller
    participant Service as TicketService
    participant Analyse as Analyseur résilient
    participant API as OpenAI
    participant Repo as Repository
    participant DB as SQLite

    Support->>Web: POST titre, description, priorité, statut
    Web->>Web: CSRF, limiteur et validation
    alt Requête refusée
        Web-->>Support: Erreur française, aucune analyse
    else Données valides
        Web->>Service: Créer avec les données validées
        Service->>Analyse: Analyser uniquement la description
        alt API activée et clé présente
            Analyse->>API: Requête structurée, délai maximal 5 s
            alt Réponse complète et valide
                API-->>Analyse: Priorité détectée et explication
            else Panne, timeout, refus ou format invalide
                Analyse->>Analyse: Appliquer les règles locales
            end
        else Mode local ou clé absente
            Analyse->>Analyse: Appliquer les règles locales
        end
        Analyse-->>Service: Recommandation, motif et source
        Note over Service,DB: Aucune transaction ouverte pendant le réseau
        Service->>Repo: Enregistrer le ticket et sa recommandation
        Repo->>DB: INSERT paramétré
        DB-->>Repo: Résultat de l’écriture
        Repo-->>Service: Ticket ou échec de persistance
        alt Ticket enregistré
            Service-->>Web: Ticket créé
            Web-->>Support: Redirection 303 vers le détail
        else Échec de persistance
            Service-->>Web: Erreur récupérable
            Web-->>Support: Formulaire et saisie conservée
        end
    end
    Note over Support,Service: La priorité humaine reste distincte de la recommandation
```

### Modèle de données

```mermaid
erDiagram
    TICKET {
        bigint id PK
        string title "1 à 160 caractères"
        text description "1 à 5000 caractères"
        string priority "low, medium, high : choix humain"
        string status "open, in_progress, resolved"
        string suggested_priority "low, medium, high : analyse"
        string analysis_reason "1 à 500 caractères"
        string analysis_source "llm ou local"
        datetime created_at
        datetime updated_at
    }
```

La référence `FT-0001` est dérivée de l’identifiant, sans colonne supplémentaire.
Les index sont `(created_at, id)`, `(status, created_at, id)` et `(priority, created_at, id)`.

<details>
<summary>Source Draw.io modifiable</summary>

[Ouvrir le fichier source](architecture.drawio).

</details>

## Responsabilités

| Composant | Rôle |
|---|---|
| Middleware web | CSRF, session et limitation des requêtes concernées |
| Validation de requête | Champs obligatoires, longueurs et valeurs autorisées |
| TicketController | Entrée HTTP, appel du service et réponse Blade/redirection |
| TicketService | Coordination de la création et de l’analyse |
| TicketRepositoryInterface | Contrat de création et de consultation filtrée |
| EloquentTicketRepository | Persistance et requêtes via le modèle Ticket |
| ResilientUrgencyAnalyzer | Appel LLM, validation du résultat et secours local |
| Adaptateur LLM | Appel HTTP et traduction de la réponse fournisseur |
| Analyseur local | Règles textuelles, explication et valeur par défaut documentées |

Le conteneur Laravel associe l’interface du repository à son implémentation. Les contrôleurs ne contiennent pas de requêtes de persistance.

## Flux de création

1. Vérifier le jeton CSRF et valider les données côté serveur.
2. Analyser la description avec un délai total court, borné et configurable.
3. Valider la priorité retournée et limiter la longueur de l’explication.
4. Utiliser le secours local si la clé manque, si l’appel échoue ou si la réponse est invalide.
5. Enregistrer le ticket et sa recommandation, puis afficher la confirmation.

Aucune transaction de base de données ne reste ouverte pendant l’appel réseau. Une erreur de base est un échec de création ; une erreur LLM déclenche le secours.

## Modèle de données

| Champ Ticket | Rôle |
|---|---|
| id | Identifiant utilisé pour la référence FT-… |
| title / description | Demande conservée comme texte |
| priority | Choix humain : low, medium, high |
| status | open, in_progress, resolved |
| suggested_priority | Recommandation : low, medium, high |
| analysis_reason | Explication courte |
| analysis_source | llm ou local |
| created_at / updated_at | Dates techniques |

Pagination de 20 tickets, ordre stable par date puis identifiant et trois index adaptés aux filtres. Les libellés français appartiennent à la présentation.

## Sécurité

- Validation serveur des créations et filtres, avec taille de description bornée.
- Requêtes Eloquent paramétrées ; aucune concaténation SQL depuis les entrées.
- Affichage Blade échappé ; description et réponse LLM traitées comme texte non fiable.
- CSRF sur les formulaires et limitation des créations pour maîtriser abus et coûts.
- Champs persistés explicitement sélectionnés après validation.
- Clé LLM côté serveur ; destination fournisseur configurée par l’application.
- Description transmise au modèle comme donnée, sans outils ni actions externes ; résultat toujours validé.
- Transmettre uniquement le texte nécessaire et documenter ce traitement.
- Journaux sans clés, descriptions complètes ou réponses brutes sensibles.
- Erreurs publiques sobres ; détails techniques réservés au développement et aux journaux.
- Sans authentification, le périmètre reste une démonstration locale. De vrais utilisateurs nécessitent des contrôles d’accès.

## Évolution

| Besoin constaté | Évolution possible |
|---|---|
| Écritures concurrentes plus nombreuses | PostgreSQL et vérification des requêtes sur cette base |
| Analyse trop lente | Enregistrement immédiat, puis job avec état pending/completed |
| Retraitement asynchrone | Idempotence et contrôle de version ; préserver la priorité humaine |
| Plusieurs instances web | Sessions/cache partagés et workers séparés si nécessaire |

Ces évolutions restent hors du premier livrable. Aucune infrastructure distribuée n’est nécessaire pour le test.

## Exécution

PHP local avec assets Vite compilés. Le navigateur ne contacte pas directement le LLM. OpenAPI pourra accompagner une future API REST ; le rendu Blade retenu n’en nécessite pas. Voir [livraison.md](livraison.md).
