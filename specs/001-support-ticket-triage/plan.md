# Implementation Plan: Fast Track Support

**Branch**: `001-support-ticket-triage` (identifiant retourné par setup-plan ; aucune branche créée ici)
**Date**: 2026-11-03 | **Spec**: [spec.md](spec.md)

**Input**: [Spécification complète et clarification du secours local](spec.md).

## Summary

Construire un monolithe Laravel avec rendu Blade fidèle au [prototype Figma](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3).
Séparer HTTP, coordination métier, persistance et analyse d’urgence. Réutiliser le layout,
les formulaires, badges et panneaux ; préserver les pages sans JavaScript.
L’analyse externe est synchrone et bornée pour ce livrable, avec secours local.

Ce plan décrit une conception, pas une implémentation réalisée ou des performances mesurées.

## Technical Context

| Élément | Décision |
|---|---|
| Language/Version | PHP 8.3+ requis, PHP 8.4.1 local ; types de paramètres et retours explicites |
| Primary Dependencies | Laravel 13.39.0 installé, Blade, Tailwind 4, Vite 8 ; pas de nouvelle dépendance |
| Storage | SQLite, migrations portables ; PostgreSQL comme évolution conditionnelle |
| Testing | Pest 5.3.5, tests HTTP et unités pures ; navigateur réel et QA manuelle |
| Target Platform | Démonstration locale, navigateur desktop et mobile 390px |
| Project Type | Application web avec rendu serveur et amélioration progressive |
| Performance Goals | SC-004 : création avec panne simulée en ≤10s ; appel externe limité à 5s, sans retry |
| Constraints | Périmètre du test, français, sécurité, absence de clé supportée, aucune authentification |
| Scale/Scope | Une file, création et détail ; pages de 20 tickets ; vérifier la pagination avec plus de 40 fixtures |

Le modèle LLM est configurable, avec `gpt-4o-mini` comme valeur initiale de l’adaptateur
OpenAI Responses. Une clé absente utilise directement le secours ; aucune clé n’est demandée
pour développer, installer ou exécuter les tests. Le compte fournisseur reste une dépendance
facultative pour une démonstration réelle du fournisseur.

## Constitution Check

Évaluation avant recherche puis après conception : conformité de conception, sans preuve runtime.

| Gate | Avant | Après | Preuve dans le plan |
|---|---|---|---|
| I. Périmètre maîtrisé | Conforme | Conforme | Quatre parcours de spec.md ; aucun ajout fonctionnel |
| II. Architecture explicite | Conforme | Conforme | Chaîne imposée, interfaces et injection décrites ci-dessous |
| III. Sécurité | Conforme | Conforme | Contrats web et analyse, contrôles CSRF, validation et secrets |
| IV. Analyse résiliente | Conforme | Conforme | Secours, timeout, réponse validée, priorité humaine distincte |
| V. Qualité et remise | Conforme | Conforme | Matrice de couverture et quickstart reproductible |
| Accessibilité et limite locale | Conforme | Conforme | Contrat des composants et démonstration locale |
| Approbations | Conforme | Conforme | Ce lot de sept fichiers a été approuvé ; code et publication ultérieurs |

## Project Structure

### Documentation (this feature)

```text
specs/001-support-ticket-triage/
├── spec.md
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── checklists/requirements.md
└── contracts/
    ├── web-routes.md
    ├── urgency-analysis.md
    └── ui-components.md
```

`tasks.md` sera généré uniquement lors de la phase suivante.

### Source Code (repository root)

Structure cible, sans création de fichiers applicatifs par cette commande :

```text
app/
├── Contracts/{TicketRepositoryInterface,UrgencyAnalyzerInterface}.php
├── Data/UrgencyResult.php
├── Enums/{TicketPriority,TicketStatus}.php
├── Http/Controllers/TicketController.php
├── Http/Requests/{StoreTicketRequest,TicketFilterRequest}.php
├── Models/Ticket.php
├── Repositories/EloquentTicketRepository.php
├── Services/TicketService.php
├── Services/Urgency/{ResilientUrgencyAnalyzer,OpenAiUrgencyAnalyzer,RuleBasedUrgencyAnalyzer}.php
└── Providers/AppServiceProvider.php
config/urgency.php
routes/web.php
resources/
├── views/components/layouts/app.blade.php
├── views/components/ui/
├── views/components/forms/
├── views/components/tickets/
├── views/tickets/{index,create,show}.blade.php
├── css/app.css
└── js/{app,panels,forms}.js
database/{migrations,factories,seeders}/
tests/{Feature,Unit}/
```

Les accolades regroupent des fichiers prévus ; elles ne sont pas des noms littéraux.

**Structure Decision**: Conserver les dossiers Laravel existants. Introduire seulement
les contrats et composants utilisés ; aucun repository générique ni framework UI supplémentaire.

## Clean code et responsabilités

- `TicketController` : méthodes index/create/store/show, réponses HTTP et redirections.
  Aucune requête Eloquent, règle de classification ou requête fournisseur dans le contrôleur.
- Form Requests : champs autorisés, normalisation, enums, limites, filtres et page.
- `TicketService` : consulter/créer, analyser avant l’enregistrement, préserver le choix humain.
  Entrées sous forme d’enums et tableaux validés décrits par PHPDoc, jamais l’objet Request.
- `TicketRepositoryInterface` : `paginate(status, priority, page)` et `find(id)`/`create(data)`.
  Retours typés Ticket ou nullable pour find, et paginator pour la liste.
- Repository Eloquent : seul accès métier au modèle ; pagination 20, tri created_at puis id décroissants.
- `UrgencyAnalyzerInterface` : `analyze(string description): UrgencyResult`.
  Résultat immuable : enum de priorité, explication de 1 à 500 caractères et source llm/local.
- `ResilientUrgencyAnalyzer` : composite injecté dans le service ; délègue à OpenAI
  et au secours. Exceptions attendues du fournisseur isolées ; les erreurs de programmation
  ne doivent pas être masquées par un catch global de toute l’application.
- `AppServiceProvider` : associer les interfaces aux implémentations. Pas de service locator
  à l’intérieur des règles métier ; pas de dépendance inverse des services vers les contrôleurs.
- Noms explicites, fonctions ciblées, visibilité minimale, enums partagés et formatage Pint.
  Extraire les répétitions réelles ; éviter les abstractions sans utilisation.

## Interface et composants

Une mise en page commune fournit langue, titre, assets Vite, header et zone principale.
Les pages passent des props et slots explicites aux composants. Aucune requête ni analyse
dans les vues. Le formulaire et le détail sont partagés entre pages et panneaux.
Voir [ui-components.md](contracts/ui-components.md) pour les composants et états.
Les couleurs sont centralisées dans le `@theme` Tailwind existant. Les mappings de badges
utilisent des classes complètes détectables lors du build.

## Sécurité et erreurs

Conserver le groupe middleware web Laravel. POST limité par IP à 10 requêtes/minute,
valeur configurable strictement positive, avec clé de limite nommée dédiée.
Valider avant l’analyse ; limiter titre/description à 160/5 000 caractères après trim.
CSRF invalide, rate limit ou validation échouée ne provoquent aucun appel externe.
Ne pas exposer de champs supplémentaires par mass assignment. Échapper ticket et explication.

Le fournisseur reçoit uniquement la description, avec instructions fixes et réponse structurée.
L’URL HTTPS est fixée dans l’adaptateur. Aucun endpoint transmis par l’utilisateur ; pas de tools.
Pas de log contenant clé, description complète ou réponse brute. Conserver seulement source,
durée, référence de ticket et catégorie d’échec maîtrisée.
Les erreurs de persistance sont traitées autour de l’enregistrement : message sobre et reprise
du formulaire, sans succès fictif ni dump de l’exception avec ses bindings sensibles.

## Scalabilité

- Pagination serveur bornée et filtres appliqués avant lecture. Pas de `all()` sur les tickets.
- Index initiaux par date/id, statut/date/id et priorité/date/id ; ajouter un index combiné
  seulement si les mesures le justifient. Le COUNT de pagination est accepté pour ce volume local.
- Aucune transaction ouverte pendant le réseau ; un enregistrement complet et atomique du ticket.
- Configuration séparée et adaptateur interchangeable pour changer de fournisseur.
- Si la latence l’exige : persister puis analyser dans un job avec état, idempotence et contrôle
  de version. Ce changement fera l’objet d’une future spécification et ne modifiera jamais
  la priorité humaine automatiquement.
- Si les écritures concurrentes augmentent : PostgreSQL et vérification de compatibilité.
  Plusieurs instances demanderont sessions/cache partagés et limiteur distribué.
- Aucun niveau de charge non mesuré n’est garanti. Files de jobs, Redis et microservices
  ne sont pas inclus dans le premier livrable.

## Couverture prévue

| Besoin | Vérification |
|---|---|
| FR-001 à FR-005 | Tests HTTP création, limites, reprise et absence d’appel en cas d’erreur ; navigateur double clic |
| FR-006 à FR-008 | Fixtures par état/priorité, filtres seuls/combinés, pagination, ordre stable et erreurs |
| FR-009 à FR-013 | Unités du secours ; tests de l’adaptateur avec HTTP simulé et appels parasites interdits |
| FR-014 | Parcours réel au clavier, 390px et desktop ; focus et états du contrat UI |
| FR-015 à FR-018 | Échappement, champs autorisés, rate limit, logs contrôlés, CSRF sur serveur réel |
| FR-019 à FR-021 | Checkout propre, migrations/seeds, README, mode sans clé et résultats consignés |

Les tests HTTP Laravel ne remplacent pas le contrôle CSRF réel : le framework peut le
court-circuiter en environnement de test. Vérifier aussi un POST sans jeton via le navigateur
ou un client HTTP contre le serveur local. Aucun achat, appel LLM réel ou package navigateur
supplémentaire n’est nécessaire pour ces contrôles.

## Complexity Tracking

Aucune dérogation à la constitution. Le repository imposé par le test n’est pas une violation.
La duplication limitée des structures tableau/cartes est justifiée par leurs sémantiques
accessibles différentes ; les badges, données et règles restent partagés.
