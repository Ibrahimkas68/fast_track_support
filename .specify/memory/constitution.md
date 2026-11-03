<!--
Sync Impact Report
Version: unfilled template → 1.0.0 (first adoption)
Principles: five project principles established; no existing principles renamed.
Added sections: Contraintes techniques; Processus de travail; Governance.
Removed: generic examples and unresolved placeholders.
Templates requiring updates:
- ✅ updated: .specify/templates/plan-template.md
- ✅ updated: .specify/templates/spec-template.md
- ✅ updated: .specify/templates/tasks-template.md
Runtime guidance: README.md links to this constitution; AGENTS.md remains compatible.
Installed Spec Kit commands: reviewed; no changes required. Future specifications
must explicitly request constitution-required tests so conditional task generation includes them.
Follow-up TODOs: none. No feature specification, plan or tasks generated.
-->

# Fast Track Support Constitution

## Core Principles

### I. Périmètre maîtrisé

Toute fonctionnalité DOIT répondre au cahier des charges ou à une décision
explicitement approuvée. Les évolutions hors périmètre DOIVENT être identifiées
séparément. La simplicité, la lisibilité et le typage priment sur le nombre
de fonctionnalités.

### II. Architecture explicite

Le backend DOIT respecter Controller → Service → Repository → Model.
La persistance DOIT passer par TicketRepositoryInterface.
Les contrôleurs DOIVENT rester centrés sur HTTP ; les services portent les
règles métier. Les dépendances DOIVENT être injectées.
Le fournisseur LLM DOIT rester isolé derrière un adaptateur.

### III. Sécurité aux frontières

Les entrées DOIVENT être validées côté serveur, les requêtes paramétrées
et les sorties utilisateur échappées. Les formulaires DOIVENT être protégés
contre le CSRF. Les créations et appels externes DOIVENT être bornés.
Les secrets DOIVENT rester côté serveur et hors du dépôt.
La réponse LLM DOIT être considérée comme non fiable et validée.

### IV. Analyse résiliente et décision humaine

Tout échec LLM, délai dépassé, réponse invalide ou absence de clé DOIT
déclencher une analyse locale. Un échec LLM NE DOIT PAS empêcher la création
d’un ticket valide lorsque la persistance fonctionne.
Aucune transaction de base ne DOIT rester ouverte pendant l’appel réseau.
La recommandation, son explication et sa source DOIVENT être conservées.
La priorité choisie NE DOIT PAS être remplacée silencieusement.

### V. Qualité démontrable et livraison reproductible

Les comportements métier DOIVENT être couverts par des tests Pest pertinents.
Les vérifications DOIVENT couvrir création, validation, filtres, sécurité
d’affichage, succès LLM et secours local.
Un parcours navigateur et une QA manuelle DOIVENT compléter les tests.
Chaque résultat annoncé DOIT correspondre à une vérification exécutée.
La livraison DOIT inclure README, migrations, seeds fictifs et configuration
sans secret, avec fonctionnement possible sans clé LLM.

## Contraintes techniques

Le socle retenu est Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4 et SQLite.
L’interface DOIT être en français et utilisable au clavier et sur mobile.
Figma est une référence visuelle ; ses analyses simulées ne prouvent pas
le fonctionnement du backend.
Les changements de dépendances, d’architecture ou de périmètre nécessitent
une justification et une approbation.
Sans authentification, l’application reste une démonstration locale.

## Processus de travail

Le travail suit : besoins validés, architecture, spécification, plan,
tâches, implémentation, revue, vérifications et livraison.
Chaque commande Spec Kit DOIT être exécutée uniquement dans le périmètre demandé.
Les tests accompagnent les fonctionnalités.
Tout changement de fichier DOIT être présenté puis approuvé avant application.
Après application, le diff et les vérifications DOIVENT être présentés.
L’approbation finale précède toute clôture, commit, publication ou déploiement.

## Governance

Cette constitution gouverne les artefacts du projet, sous réserve des
instructions explicites de l’utilisateur.
Toute modification DOIT préciser sa justification, son impact et les
artefacts à synchroniser, puis recevoir une approbation.
Chaque revue DOIT vérifier la conformité aux principes.
Une dérogation DOIT être documentée et approuvée.
Versionnement : MAJOR pour une rupture de gouvernance, MINOR pour un ajout
substantiel, PATCH pour une clarification sans changement de règle.

**Version**: 1.0.0 | **Ratified**: 2026-11-03 | **Last Amended**: 2026-11-03
