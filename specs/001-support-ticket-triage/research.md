# Recherche et décisions

Date : 2026-11-03. Les décisions ci-dessous sont résolues pour le premier livrable.
Aucun test applicatif ni appel fournisseur réel n’a été exécuté pendant cette phase.

| Sujet | Décision | Justification | Alternatives examinées |
|---|---|---|---|
| Architecture | Monolithe Laravel, responsabilités séparées | Socle existant et quatre parcours limités | Microservices et frontend séparé : complexité sans besoin mesuré |
| Contrats | Repository et analyzer typés | Respect du test et remplacement du fournisseur | Repository générique ou service locator : responsabilités moins explicites |
| Composants | Blade anonymes, props, slots et attributs | Réutilisation sans nouvelle dépendance | Copier les pages ou construire une bibliothèque universelle : duplication ou surabstraction |
| Panneaux | Amélioration progressive avec dialog natif | Pages utilisables sans JS, contenu partagé | SPA : état client et validation dupliqués |
| Styles | Tailwind 4 et tokens CSS existants | Cohérence avec le socle et Figma | Nouvelle bibliothèque UI : installation et adaptation inutiles |
| Fournisseur | OpenAI Responses via HTTP Laravel | Contrat structuré et aucun SDK supplémentaire | SDK ou plusieurs fournisseurs : intégration plus large |
| Modèle | Configurable, initialement gpt-4o-mini | Modèle documenté pour les sorties structurées | Choix automatique du modèle : coûts et comportements moins prévisibles |
| Résilience | Connexion 2s, réponse 5s, zéro retry | Borne compatible avec SC-004 | Retries synchrones : multiplication du délai et du coût |
| Secours | Règles françaises selon l’impact | Reproductible, explications inspectables | Mot urgent seul ou moyenne systématique : contredit la clarification |
| Données | SQLite et migrations portables | Remise locale simple | PostgreSQL au départ : service supplémentaire pour le destinataire |
| Pagination | 20 résultats et ordre date/id | Lecture bornée et navigation stable | Tout charger : croissance non maîtrisée ; cursor : total moins pratique ici |
| Navigateur | Contrôle réel et QA manuelle | Aucun plugin navigateur installé | Installer un outil E2E : dépendance et téléchargement supplémentaires |

## Sources

- Versions installées vérifiées avec `composer show --direct` : Laravel 13.39.0, Pest 5.3.5.
- [Laravel : composants Blade](https://laravel.com/framework/docs/13.x/blade)
- [Laravel : client HTTP, timeouts et simulations](https://laravel.com/framework/docs/13.x/http-client)
- [Laravel : pagination et query string](https://laravel.com/framework/docs/13.x/pagination)
- [OpenAI : sorties structurées et refus](https://developers.openai.com/api/docs/guides/structured-outputs)
- Références locales : `.agents/skills/laravel-best-practices/rules/blade-views.md`,
  `.agents/skills/laravel-best-practices/rules/http-client.md`,
  `.agents/skills/tailwindcss-development/SKILL.md` et
  `.agents/skills/testing-best-practices/SKILL.md`.

Les sorties structurées réduisent les erreurs de format mais n’éliminent pas les refus,
réponses incomplètes ni pannes ; l’application valide toujours le résultat.
Le contexte de recherche frontend a été revu par un agent de recherche en lecture seule.
Le prototype est une référence de présentation, pas un backend réutilisable tel quel.
