# Installation et validation

Ce guide décrit le livrable local. Voir [les résultats exécutés](../../docs/verification.md)
pour distinguer les contrôles réalisés et les limites.

## Prérequis

PHP 8.4.1+ avec SQLite et mbstring, Composer, Node.js 22.12+ et npm. Une clé LLM est facultative.
Exécuter depuis la racine du projet, sur un checkout propre dédié à la validation.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm ci
npm run build
php artisan serve
```

Configurer `APP_URL` avec l’adresse réellement utilisée. Ouvrir cette adresse puis `/tickets`.
Garder `URGENCY_DRIVER=local` pour une démonstration sans fournisseur.
Voir [le contrat d’analyse](contracts/urgency-analysis.md) pour l’option externe.

## Vérifications automatisées

```bash
php artisan test --compact tests/Unit/RuleBasedUrgencyAnalyzerTest.php
php artisan test --compact tests/Feature/TicketCreationTest.php
php artisan test --compact tests/Feature/TicketListingTest.php
php artisan test --compact tests/Feature/UrgencyAnalysisTest.php
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
```

Les tests existent et utilisent SQLite en mémoire. Les scénarios fournisseur interdisent
les appels HTTP non simulés. Les tests PHP ne nécessitent pas le build Vite.
Ne pas lancer de tests contre une base de démonstration contenant des données utiles.

## Parcours navigateur

| Contrôle | Résultat attendu |
|---|---|
| Créer un ticket valide | Confirmation, référence et détail persistants ; moins de 2 minutes avec texte préparé |
| Soumettre un formulaire invalide | Erreurs associées aux champs, saisie conservée, aucun ticket ajouté |
| Double clic sur création | Un envoi actif, bouton temporairement désactivé |
| Filtres seuls et combinés | Résultats et total corrects, pagination conserve les filtres |
| Description de panne complète | Recommandation Haute, priorité humaine conservée |
| Dégradation avec contournement | Recommandation Moyenne |
| Défaut visuel / information | Recommandation Basse |
| Signaux inconnus / niés | Moyenne sans signal ; pas de Haute déclenchée par un signal nié |
| Panne fournisseur simulée | Résultat local et explication, création en ≤10s sur environnement local fonctionnel |
| Ticket contenant du HTML | Texte affiché, aucun script exécuté |
| Mobile 390px et clavier | Cartes lisibles, aucun débordement horizontal, focus et panneaux utilisables |
| JavaScript désactivé | Pages création et détail utilisables par navigation normale |

## Sécurité et panne réelle

Vérifier contre le serveur local un POST sans jeton de formulaire valide : refus 419
et aucun ticket ni appel fournisseur. Les tests HTTP Laravel peuvent désactiver ce contrôle,
ils ne constituent donc pas seuls cette preuve.
Dépasser la limite de création : réponse 429 compréhensible. Les tests automatisés de
limitation utilisent un état de limiteur isolé et vérifient l’absence d’appel externe.
Tester une panne de persistance contrôlée uniquement sur une copie de validation :
aucun faux succès ni affichage d’erreur interne. Ne pas modifier les droits de la base principale.

## Remise

Suivre [la checklist de livraison](../../docs/livraison.md). Vérifier les liens Figma en
lecture avec le destinataire, conserver les lockfiles et exclure les secrets et données locales.
Consigner commandes, résultats, limites et mesures réellement obtenues. Le déploiement
n’est pas requis par le test et nécessite une approbation distincte.
