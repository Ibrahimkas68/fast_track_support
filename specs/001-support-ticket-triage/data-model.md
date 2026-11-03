# Modèle de données

Conception cible ; aucune migration n’a encore été créée par la phase de planification.

## Ticket

| Champ | Type cible | Contraintes |
|---|---|---|
| id | Entier auto-incrémenté | Clé primaire |
| title | Chaîne, 160 caractères | Requis, trim, longueur 1–160 |
| description | Texte | Requis, trim, longueur 1–5 000 |
| priority | Chaîne, 16 | low / medium / high ; enum PHP |
| status | Chaîne, 16 | open / in_progress / resolved ; enum PHP |
| suggested_priority | Chaîne, 16 | low / medium / high ; non nullable |
| analysis_reason | Chaîne, 500 | Explication non vide, 1–500 caractères |
| analysis_source | Chaîne, 16 | llm / local |
| created_at / updated_at | Dates | Stockage UTC, présentation Africa/Casablanca |

La référence affichée est `FT-` suivi de l’identifiant avec au moins quatre chiffres.
Elle est dérivée, non stockée et ne promet pas une séquence sans trous.
Les recommandations sont stockées avec le ticket : aucune table liée n’est nécessaire.

## Validation et intégrité

Valider les enums côté serveur, appliquer les casts du modèle et des contraintes de
valeurs au niveau base lorsque disponibles de manière portable dans les migrations retenues.
Les champs écrits sont explicitement sélectionnés ; seule l’analyse fournit les trois
champs de recommandation. Aucun champ de recommandation provenant du formulaire n’est accepté.
La sauvegarde comprend ticket et recommandation dans la même écriture.

## Index et consultation

- Clé primaire sur id.
- Index `(created_at, id)` pour la file.
- Index `(status, created_at, id)` et `(priority, created_at, id)` pour les filtres.
- Tri `created_at DESC, id DESC`, 20 résultats par page.
- Filtres combinés par AND ; total calculé sur la sélection filtrée.

## Cycle de vie

La création peut choisir chacun des trois statuts autorisés. Aucun changement de statut,
édition, suppression, assignation ou relance d’analyse n’est exposé dans ce périmètre.
Le ticket est soit enregistré avec un résultat llm/local complet, soit non créé.
Il n’existe aucun état pending dans la version synchrone.

## Objets non persistés

`UrgencyResult` : résultat immuable composé de la priorité recommandée, du motif et de
la source. Les critères de filtre restent des valeurs validées, pas une nouvelle table.

## Seeds

Créer des données fictives couvrant les neuf combinaisons statut/priorité, des résultats
llm/local et des dates identiques pour prouver le tri secondaire. Un résultat de seed llm
est explicitement une fixture, pas la preuve d’un appel réel. Ne pas contacter le fournisseur.
