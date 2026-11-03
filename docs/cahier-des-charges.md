# Cahier des charges

## Objectif

Concevoir un micro-système de gestion de tickets avec analyse automatique de l’urgence, destiné au support interne.

## Exigences du test

| Référence | Exigence |
|---|---|
| F01 | Créer un ticket : titre, description, priorité et statut |
| F02 | Priorités : Basse, Moyenne, Haute |
| F03 | Statuts : Ouvert, En cours, Résolu |
| F04 | Lister les tickets et filtrer par statut ou priorité |
| F05 | Détecter l’urgence depuis la description via API LLM ou analyse textuelle, avec secours si l’API échoue |
| A01 | Respecter Controller → Service → Repository → Model |
| A02 | Utiliser TicketRepositoryInterface |
| S01 | Requêtes paramétrées, validation serveur stricte et sorties échappées |
| L01 | Fournir un dépôt GitHub/GitLab public ou un ZIP, avec README, migrations et seeds |

Le courriel prévoit environ quatre heures consécutives et une remise dans les 24 heures suivant réception. Il privilégie la lisibilité, le typage et l’organisation.

## Décisions proposées

- Option A : Laravel avec rendu Blade et JavaScript léger.
- Interface en français, adaptée au desktop et au mobile.
- Appel LLM côté serveur, avec analyse locale de secours.
- Analyse déclenchée à la création ; priorité choisie distincte de la recommandation.
- Conservation de la priorité suggérée, de son explication et de sa source.
- Filtres statut et priorité combinables.

La distinction entre priorité choisie et recommandation est une interprétation du besoin à valider avant implémentation.

## Hors périmètre initial

Authentification, équipes, commentaires, pièces jointes, notifications, SLA et statistiques. La modification et la suppression de tickets ne sont pas explicitement demandées.

## Résultat attendu

Créer une demande, la retrouver dans la liste et consulter une recommandation compréhensible. Une panne LLM ne bloque pas la création. Le projet doit fonctionner sans clé LLM grâce au secours local.

[Prototype Figma](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3) : tickets et analyses fictifs.
