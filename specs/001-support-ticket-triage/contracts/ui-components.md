# Contrat UI et composants

Référence : [prototype Figma](https://www.figma.com/design/zSb6GQW643fxGDIT9Y5eNp/Untitled?node-id=2-3). Les analyses de Figma restent simulées.

## Layout et tokens

`components/layouts/app.blade.php` fournit `lang=fr`, titre, assets Vite, header partagé,
messages flash et slot principal. Slots nommés pour titre/actions si utiles ; aucune règle métier.
Conserver Instrument Sans, la palette du [document de design](../../../docs/figma-make-prompt.md),
la largeur de contenu maximale 1280px et des gouttières mobiles. Tokens dans `app.css @theme`.

Le header contient nom, Support interne et Créer un ticket. La page Tickets présente
compteur, filtres et liste ; aucune navigation ou statistique hors périmètre.

## Interfaces des composants

| Composant cible | Props / slots | Responsabilité |
|---|---|---|
| ui/button | variant, type ; slot label ; attributs aria/disabled | Action cohérente et focus visible |
| ui/alert | variant, titre ; slot contenu | Confirmation ou erreur compréhensible |
| ui/empty-state | titre, description ; slot action | Liste vide ou filtre sans résultat |
| ui/panel | id, title ; contenu chargé par le script | Panneau dialog avec nom accessible |
| forms/field | name, label, help, error ; slot contrôle | Label, aide et erreur reliés au contrôle |
| forms/input, select, textarea | id, name, value ; attributs ; options/slot | Contrôles natifs réutilisables |
| tickets/priority-badge | enum priority | Libellé, icône et couleur de priorité |
| tickets/status-badge | enum status | Libellé, icône et couleur de statut |
| tickets/form | action, errorBag ; ancienne saisie de session | Un seul formulaire pour page et panneau |
| tickets/detail | ticket | Même contenu pour page et panneau |
| tickets/urgency-analysis | priority, reason, source | Recommandation, motif, provenance et avertissement |

Utiliser les conventions Blade `@props`, slots et attribute bag. Éviter les props implicites,
les requêtes dans les composants et les composants universels à nombreux flags.
Extraire un composant seulement s’il a une interface utile ou une réutilisation réelle.
Les pages index/create/show assemblent les composants ; elles ne contiennent pas de logique d’analyse.

## Responsive et interactions

- Desktop : vrai tableau avec référence, titre/extrait, priorité, statut et date.
- Mobile 390px : cartes avec liens accessibles ; pas de tableau forcé hors écran.
- Réutiliser badges et données préparées ; la duplication limitée du balisage tableau/cartes
  est autorisée pour préserver leur sémantique. Une seule requête paginée alimente les deux.
- Titres de tickets : vrais liens ; ouverture au clavier, focus visible et intitulé explicite.
- Un formulaire GET fournit les filtres. Avec JavaScript, la sélection peut soumettre ce
  même formulaire ; un bouton Appliquer reste accessible sans JavaScript.
- Créer/détail : liens canoniques, améliorés par `dialog.showModal()` et fragments HTML
  same-origin ; focus initial, fermeture Échap et retour au déclencheur.
- Soumission normale du formulaire : navigation serveur après réussite ou erreur de validation.
  Bloquer les soumissions simultanées sans désactiver les valeurs nécessaires à l’envoi.
- `panels.js` gère chargement et focus ; `forms.js` gère l’état d’envoi. Aucun état métier
  dupliqué côté client, aucun appel fournisseur depuis JavaScript.

La pagination utilise `tickets/pagination` avec la prop `paginator`, des liens
Précédent/Suivant et un libellé de page en français. Les identifiants des filtres sont
`filter-status` et `filter-priority`, distincts de ceux du formulaire dans le panneau.

## États obligatoires

Validation par champ avec ancienne saisie, focus sur première erreur, envoi en cours,
confirmation, échec récupérable, absence de ticket, absence de résultat, détail introuvable.
Source llm : « Analyse par modèle de langage ». Source local : « Analyse locale de secours ».
En secours afficher : « Le service d’analyse est indisponible ou non configuré.
Une analyse locale a été utilisée. » Préserver la priorité choisie séparément.

Contrôles étiquetés, messages reliés par aria-describedby, couleurs accompagnées de texte,
cibles tactiles ≥44px, mouvement réduit respecté. Vérifier contrastes et débordements à
l’intégration ; les seules captures Figma ne prouvent pas l’accessibilité.
