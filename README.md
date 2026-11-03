# Fast Track Support

**[Découvrir le prototype Figma](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3)**

Gestion de tickets de support avec recommandation automatique d’urgence.

## État du projet

Socle Laravel initialisé et prototype interactif disponible. Les fonctionnalités métier et l’intégration LLM restent à développer. Les analyses Figma sont simulées.

## Documentation

| Document | Contenu |
|---|---|
| [Constitution](.specify/memory/constitution.md) | Principes et règles de gouvernance |
| [Cahier des charges](docs/cahier-des-charges.md) | Besoin, périmètre et contraintes |
| [Architecture](docs/architecture-docs.md) | Frameworks, composants, données et sécurité |
| [Diagrammes Draw.io](docs/architecture.drawio) | Architecture, création et modèle de données |
| [Design et prompt Figma](docs/figma-make-prompt.md) | Référence visuelle et composants |
| [Livraison](docs/livraison.md) | Contenu et vérifications de la remise |

La spécification d’implémentation sera préparée après validation de ces documents.

## Stack retenue

Laravel 13 · PHP 8.3+ · Blade · Tailwind CSS 4 · SQLite · Pest.

## Lancer le socle localement

Prérequis : PHP 8.3+, Composer, extension SQLite et Node.js compatible avec Vite 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm ci
npm run build
php artisan serve
```

Ces commandes concernent le socle actuel. Les migrations et seeds de tickets ainsi que la configuration LLM seront documentés lors de leur implémentation.
