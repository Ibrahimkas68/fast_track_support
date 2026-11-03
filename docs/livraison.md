# Livraison

## Parcours du destinataire

1. Ouvrir Figma depuis le premier lien du README ; les analyses du prototype sont simulées.
2. Lire le cahier des charges et l’architecture.
3. Suivre les commandes du README, puis ouvrir `/tickets`.
4. Créer une demande, filtrer la file et consulter les deux priorités.
5. Exécuter les tests et consulter [les résultats](verification.md).

## Contenu vérifié

- [x] Sources et lockfiles Composer/npm.
- [x] README : prérequis réels, installation, lancement et tests.
- [x] Migration et neuf seeds fictifs rejouables sans doublon.
- [x] `.env.example` sans secret, analyse locale par défaut et configuration LLM facultative.
- [x] Contrats et documentation du comportement implémenté.
- [x] Résultats de vérification et limites connues.
- [x] Dépôt GitHub distant configuré et accessible sans authentification.

## Vérifications réalisées

- [x] Installation depuis une archive Git propre, avec dépendances verrouillées.
- [x] Création, invalides, filtres combinés, pagination et rechargement.
- [x] Priorité humaine conservée séparément.
- [x] Succès LLM, absence de clé, timeout et réponses invalides simulés.
- [x] Fonctionnement local sans clé et sans JavaScript via client HTTP.
- [x] Échappement, CSRF réel 419 et limiteur réel 429.
- [x] Desktop 1440px, mobile 390px, clavier, focus et absence de débordement.
- [x] Panne de persistance contrôlée sur copie jetable, sans perte de la saisie ni faux succès.
- [x] Tests exécutés, résultats dans verification.md.

## Limites de remise

L’accès Figma avec le compte connecté a été vérifié précédemment ; l’accès anonyme du
destinataire reste à contrôler par le propriétaire. Le connecteur n’a pas rendu le code layer
pendant l’implémentation : pas de promesse de correspondance pixel à pixel.
Aucun appel LLM réel, test PostgreSQL ou test de charge n’a été effectué.
L’application sans authentification est destinée à une démonstration locale.

## Contenu exclu

`.env` et variantes, clés, bases SQLite locales, journaux, vendor, node_modules et build
sont exclus du dépôt. Le destinataire installe les dépendances et compile les assets.
L’archive ZIP propre peut être produite avec `git archive --format=zip HEAD -o ../fast_track_support.zip`.
Aucun email ni déploiement n’est envoyé automatiquement.
