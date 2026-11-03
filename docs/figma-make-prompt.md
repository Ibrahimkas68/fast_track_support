# Design et prompt Figma

**[Ouvrir le prototype](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3)**

Les données et analyses sont simulées. Le code du prototype ne remplace pas l’application PHP.

## Direction visuelle

Interface française : tableau desktop, cartes mobile, formulaire de création et détail. Priorité choisie, recommandation et source restent distinctes.

Palette : fond #F5F7FA, surfaces #FFFFFF, texte #182230, texte secondaire #526174, bordures #DCE3EB, action #2457D6. Instrument Sans ou sans-serif lisible, corps 14 à 16 px. Vérifier les contrastes à l’intégration.

## Composants

Boutons, champs, textarea, sélecteurs, badges priorité/statut, ligne de ticket, carte mobile, panneaux création/détail, validation, confirmation, chargement et états vides.

## Prompt de référence

```text
Refine the existing Fast Track Support prototype. All UI copy must be natural French.

Keep the scope: create tickets, list them, filter by status and priority, inspect a ticket and its urgency recommendation. Preserve the blue/white identity and use reusable components suitable for Blade and Tailwind.

Use a compact header with the product name, Support interne and Créer un ticket. Desktop: semantic table with reference, title/description excerpt, priority, status and date. Sentence case headings, body text 14–16px, responsive gutters and maximum content width 1280px. At 390px use readable ticket cards and full-width panels without horizontal scrolling.

Creation panel: title, description, priority (Basse, Moyenne, Haute), status (Ouvert, En cours, Résolu), Créer le ticket and Annuler. Show field validation, preserve entered values after errors and prevent duplicate submissions. Explain that urgency is analyzed after saving.

Details: distinguish selected priority from recommended urgency and explain the result. Show simulated sources Analyse par modèle de langage and Analyse locale de secours. For fallback display: Le service d’analyse est indisponible. Une analyse locale a été utilisée. Never override selected priority silently. Do not connect a real API or embed credentials.

Use fictional November 2026 data. Keep correct singular/plural counts updated with filters. Include empty, no-results, loading, success and recoverable-error states.

Provide keyboard-operable ticket buttons, visible focus, panel focus management, Escape close and focus return. Mobile touch targets at least 44px, readable contrast, reduced-motion support and no color-only meaning.

Do not add search, charts, authentication, teams, SLA, attachments, chat or comments. The final backend remains Laravel/PHP regardless of the prototype's internal framework.
```
