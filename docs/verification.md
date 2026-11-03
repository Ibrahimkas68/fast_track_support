# Vérifications

## Lot de configuration, 2026-11-03

Périmètre : T001 et T002. Configuration uniquement ; aucune fonctionnalité ticket implémentée.

| Contrôle | Résultat |
|---|---|
| Checklist requirements.md | 16 éléments complétés, aucun restant |
| Dépendances installées | Laravel 13.39.0, Pest 5.3.5 |
| Configuration PHP | `php -l config/urgency.php` réussi ; chargement isolé et six valeurs par défaut vérifiés |
| Tests du socle | `php artisan test --compact` : 2 tests réussis, 2 assertions |
| Build frontend | `npm run build` réussi, Vite 8.3.5 |
| Formatage PHP | `vendor/bin/pint config/urgency.php --format agent` réussi |
| Secrets et lockfiles | Empreintes SHA-256 de `.env`, `composer.lock` et `package-lock.json` identiques avant/après |

`pint --dirty --format agent` ne fonctionne pas ici car le dossier ne possède pas
de dépôt Git. Le contrôle a donc été exécuté explicitement sur le seul fichier PHP
modifié. Aucun dépôt n’a été initialisé.

Le build émet un avertissement : le package facultatif `fontaine` manque pour
l’optimisation des polices de secours. Le build réussit ; aucune dépendance n’a été ajoutée.

Les tests métier seront créés dans les tâches suivantes. Les exemples du socle
ne prouvent pas le fonctionnement de la création, des filtres ou de l’analyse.
Les variables de `.env.example` décrivent une nouvelle installation ; le fichier
`.env` de travail reste inchangé. Les contrôles de valeurs autorisées et de bornes
seront ajoutés aux consommateurs de configuration dans les tâches d’implémentation.

## Types métier, 2026-11-03

Périmètre : T003. Enums de priorité/statut et résultat d’analyse immuable.
Le ticket, sa persistance et les analyseurs ne sont pas encore implémentés.

| Contrôle | Résultat |
|---|---|
| Tests avant implémentation | 11 cas sans succès : classes absentes, résultat attendu à cette étape |
| Tests unitaires ciblés | `php artisan test --compact tests/Unit/UrgencyResultTest.php` : 11 cas réussis, 15 assertions |
| Suite complète | `php artisan test --compact` : 13 tests réussis, 17 assertions |
| Libellés des enums | Six libellés français vérifiés par chargement PHP isolé |
| Formatage | Pint réussi sur les quatre fichiers PHP ajoutés |

Les cas unitaires couvrent les sources autorisées/interdites, la priorité conservée,
le trim du motif et les limites de 1, 500 et 501 caractères Unicode.
Aucune base de données ni API externe utilisée par ces nouveaux tests.

## Persistance des tickets, 2026-11-03

Périmètre : T004. Migration, modèle Eloquent, factory et tests de persistance.
Les routes, le repository et les analyseurs ne sont pas encore implémentés.

| Contrôle | Résultat |
|---|---|
| Tests avant implémentation | 10 cas en échec attendu : modèle Ticket absent |
| Tests de persistance ciblés | `php artisan test --compact tests/Feature/TicketPersistenceTest.php` : 10 cas réussis, 28 assertions |
| Suite complète | `php artisan test --compact` : 23 tests réussis, 45 assertions |
| Migration SQL | `migrate --pretend` sur SQLite en mémoire : table, contraintes de valeurs et trois index générés |
| Formatage | Pint réussi sur les quatre fichiers PHP ajoutés |

Les tests vérifient les données après relecture, les casts, la séparation des priorités,
la factory locale, les références FT-0001/FT-9999/FT-10000, le refus de valeurs
hors liste et le refus d’un ticket sans motif d’analyse. La référence n’est pas stockée.
Les enums de migration produisent des contraintes CHECK sur SQLite ; les limites de
longueur des textes seront aussi validées côté application, car SQLite n’impose pas
les longueurs VARCHAR déclarées.

Les tests utilisent `LazilyRefreshDatabase` et la configuration SQLite `:memory:`
de phpunit.xml. Aucune migration n’a été exécutée sur la base locale de travail.
La compatibilité PostgreSQL n’a pas été exécutée ni revendiquée.

## Contrats et repository, 2026-11-03

Périmètre : T005. Contrats typés de persistance et d’analyse, repository Eloquent.
L’association des interfaces au conteneur reste prévue dans T006. Aucun analyseur,
contrôleur ou service métier n’est ajouté par ce lot.

| Contrôle | Résultat |
|---|---|
| Tests avant implémentation | 24 cas en échec attendu : repository absent |
| Tests ciblés | `php artisan test --compact tests/Feature/EloquentTicketRepositoryTest.php` : 24 cas réussis, 57 assertions |
| Suite complète | `php artisan test --compact` : 47 tests réussis, 102 assertions |
| Formatage | Pint réussi sur les quatre fichiers PHP ajoutés |
| Espaces | `git diff --check` réussi |

Les tests utilisent SQLite en mémoire et vérifient création par champs explicites,
exclusion des identifiants/dates/références injectés, recherche et absence, trois statuts,
trois priorités, neuf combinaisons AND, tri date/id, 45 tickets répartis sur trois pages,
filtres conservés sans paramètre arbitraire et états vides. La priorité humaine reste
distincte de la recommandation. Aucun appel externe ni migration sur la base de travail.

## Middleware et erreurs, 2026-11-03

T006 : résolution du repository, limiteur par IP et erreurs 404/419/422/429 en français.
Les 13 cas de FoundationTest passent (30 assertions). Suite : 60 tests, 132 assertions.
Les réponses JSON et les en-têtes Retry-After/X-RateLimit sont préservés. Les détails
d’exception restent masqués même avec debug actif. Pint réussi. Le limiteur est
enregistré ; son attachement à la route de création sera réalisé dans T013.

## Création et analyse locale, 2026-11-03

T007 à T014 : composants Blade, formulaire, validation, service et analyse locale.
19 cas unitaires de classification passent (73 assertions), dont les exemples de négation.
25 cas de création passent (151 assertions), avec limites Unicode, entrée forgée refusée,
saisie conservée et panne de persistance récupérable. Suite : 104 tests, 356 assertions.
Pint et build Vite réussis. Le suivi de session des tests multi-requêtes utilise le cookie
réel de session pour reproduire le navigateur avec la sérialisation JSON Laravel 13.

Le connecteur Figma a retourné les références du code layer mais n’a pas permis leur
lecture ni le rendu du nœud. La présentation utilise la capture fournie et le contrat UI.
Aucune revendication de correspondance pixel à pixel. Les contrôles navigateur de T015
et l’intégration des panneaux restent à exécuter.
