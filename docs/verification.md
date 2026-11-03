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
