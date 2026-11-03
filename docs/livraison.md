# Livraison

## Parcours du destinataire

1. Ouvrir Figma depuis le lien placé en tête du README.
2. Lire le cahier des charges et les choix d’architecture.
3. Installer le projet, exécuter les migrations et charger les seeds.
4. Essayer les tickets, filtres et recommandations.
5. Examiner les tests et le fonctionnement sans clé LLM.

Vérifier manuellement l’accès en lecture au prototype avant l’envoi. Figma complète le livrable PHP.

## Contenu attendu

- [ ] Sources et fichiers de verrouillage Composer/npm.
- [ ] README : versions, installation, lancement et commandes de tests.
- [ ] Migrations et seeds de tickets fictifs.
- [ ] .env.example sans secret, configuration LLM et secours local documentés.
- [ ] Documentation conforme au comportement réellement implémenté.
- [ ] Résultats des vérifications et limites connues.
- [ ] Dépôt GitHub/GitLab public ou archive ZIP propre.

## Contrôles avant remise

- [ ] Installation depuis un checkout propre.
- [ ] Création valide, données invalides et filtres combinés.
- [ ] Priorité humaine distincte de la recommandation.
- [ ] Succès LLM, clé absente, timeout et réponse invalide.
- [ ] Secours local disponible sans clé.
- [ ] Sorties échappées et formulaires protégés.
- [ ] Parcours desktop/mobile et navigation clavier.
- [ ] Tests exécutés avec résultats consignés.

## Nettoyage

Exclure .env, clés, base locale, journaux, vendor et node_modules. Examiner les configurations d’assistants avant publication. Ne cocher aucun contrôle avant son exécution.

Un hébergement et une courte vidéo sont facultatifs. L’installation locale reproductible reste la priorité.
