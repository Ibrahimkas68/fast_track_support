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

## File filtrée, 2026-11-03

T016 à T019 : 20 tests HTTP passent (77 assertions), avec neuf combinaisons, erreurs
422, pagination et états vides. Suite : 124 tests, 434 assertions ; Pint réussi.
Le test du scaffold vérifie désormais la redirection accueil vers /tickets.
Les contrôles de navigation réels de T020 restent à exécuter.

## Analyse externe résiliente, 2026-11-03

T021 à T026 : adaptateur Responses et composite avec secours local. Suite : 161 tests,
555 assertions. Les tests vérifient schéma strict, store=false, un seul envoi, URL fixe,
absence de redirection, timeout 5s et connexion 2s, refus, sortie incomplète, JSON invalide,
limites de taille, erreurs HTTP et échec de connexion simulé. Les créations en secours
simulé respectent l’objectif de 10s dans ces tests locaux. Aucun délai réseau réel mesuré.
Aucun appel LLM réel effectué. Les HTTP fakes interdisent les appels parasites.
Les sources et motifs sont conservés, les motifs échappés, la priorité humaine inchangée.
Les journaux ne contiennent que source, durée et catégorie maîtrisée. Pint réussi.

## Données de démonstration et panneaux, 2026-11-03

T027 : neuf combinaisons statut/priorité, rechargement des seeds sans doublon ni appel
fournisseur. Les deux exemples de source llm sont marqués fictifs dans leur motif.
Les fragments HTML create/show utilisent les composants des pages complètes et gardent
le jeton CSRF. Les tests de sécurité couvrent échappement, ancien input tableau, entrée
invalide avant réseau et limitation de la vraie route POST avant un second appel externe.
Suite : 168 tests, 601 assertions ; Pint réussi. Les tests navigateur et CSRF sur serveur
réel restent à exécuter avant de clôturer T030/T031/T032.

## Validation finale sur copie isolée, 2026-11-03

| Contrôle | Preuve et résultat |
|---|---|
| Installation propre | Archive Git sans vendor/node_modules/.env ; Composer installe 132 paquets et npm 98 depuis les lockfiles |
| Commandes | Copie .env.example, key:generate, création SQLite et migrate --seed réussis |
| Build propre | Premier essai bloqué par le réseau sandbox vers fonts.bunny.net ; nouvel essai autorisé réussi |
| Tests propres | Après isolation de Vite dans TestCase, 169 tests passent ; le build reste vérifié séparément |
| Browser desktop | Table visible à 1440px ; document de largeur 1440px, sans débordement |
| Browser mobile | Cartes à 390px ; document de largeur 390px, panneau de 358px ; aucun débordement constaté |
| Création navigateur | Ticket FT-0010, priorité Basse et recommandation Haute ; rechargement persistant |
| Double clic réel | Exactement un ticket « Validation navigateur locale » dans la base QA |
| Validation réelle | Titre composé d’espaces refusé ; description conservée et focus sur le titre invalide |
| Clavier | Entrée ouvre les panneaux, Tab/Shift+Tab restent à l’intérieur, Échap ferme et restaure le focus |
| Pagination réelle | 44 tickets QA, navigation de page 2 vers page 3, libellés français et liens mobiles ≥44px |
| CSRF réel | POST sans jeton : 419 français et zéro ticket ajouté |
| Limiteur réel | 429 avec Retry-After ; une requête bloquée supplémentaire ajoute zéro ticket |
| Sans JavaScript | Client HTTP avec cookies/CSRF : refus invalide puis création et détail HTML réussis |
| Stockage indisponible | Trigger temporaire sur la seule copie QA : erreur récupérable, saisie conservée, zéro insertion ; trigger retiré |
| Contrastes | Neuf paires de couleurs testées mathématiquement : rapports de 5,89:1 à 16,03:1, tous ≥4,5:1 |
| Console navigateur | Aucun message d’erreur observé pendant le parcours testé |
| Dépôt public | Page GitHub accessible sans authentification, réponse HTTP 200 |

Corrections issues de QA : identifiants distincts pour filtres/formulaire, boucle de focus
explicite dans le dialog, cibles mobiles de 44px, pagination française et seeds datés
relativement au passé. Les tests Blade ne dépendent plus d’un manifeste Vite préexistant.
Le README précise PHP 8.4.1+, exigé par les versions verrouillées, et Node 22.12+.
Le build garde un avertissement facultatif fontaine ; aucune dépendance ajoutée.
Les vérifications HTTP se déroulent en environnement local réel, sans désactiver CSRF,
avec URGENCY_DRIVER=local. La base et le fichier .env du projet de travail restent inchangés.

Limites : aucune API payante réelle, aucun PostgreSQL ni test de charge, aucun audit
exhaustif d’accessibilité. Accès Figma anonyme confirmé lors de la revue avant remise ci-dessous.
Le temps de saisie n’a pas fait l’objet d’une mesure chronométrée formelle ; la borne de
10s est vérifiée seulement pour les pannes simulées et les options du client HTTP.

## Revue de remise

Résultat final : 169 tests réussis, 612 assertions ; Pint, Vite et git diff --check
réussis. Liens Markdown locaux vérifiés. Scan ciblé des fichiers suivis : aucune clé
privée ni signature de jeton détectée ; .env, bases SQLite et dépendances non suivis.
Les empreintes de .env et des deux lockfiles sont identiques à celles avant implémentation.
Aucun hook d’extension configuré. La poursuite et les commits autonomes ont été
explicitement autorisés par l’utilisateur, sans demandes d’approbation répétées.

## Revue avant remise, 2026-11-03

- Tests relancés : 169 réussis, 612 assertions.
- Build Vite réussi ; avertissement facultatif fontaine inchangé.
- `composer validate --strict --no-check-publish` réussi.
- `composer audit --locked` en ligne : aucune alerte ni dépendance abandonnée signalée.
- `npm audit` en ligne : zéro vulnérabilité signalée.
- Le commit distant main correspond au livrable revu ; dépôt public et diagrammes Mermaid rendus.
- Figma ouvert sans connexion : fichier et prototype visibles ; aucune modification des permissions.
- Les migrations, seeds, instructions et contrats correspondent au périmètre du test.

Aucun blocage identifié pour la remise du test technique. L’intégration OpenAI reste
vérifiée avec réponses simulées, sans appel payant réel. Cette revue ne constitue pas
un audit de sécurité exhaustif ni une validation de déploiement en production.
