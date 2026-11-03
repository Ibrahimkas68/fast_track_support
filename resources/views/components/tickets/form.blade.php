@props(['action', 'errorBag'])
<form method="POST" action="{{ $action }}" data-ticket-form class="space-y-6">
    @csrf
    @if($errorBag->has('storage'))<x-ui.alert variant="error">{{ $errorBag->first('storage') }}</x-ui.alert>@endif
    @if($errorBag->any())<p class="text-sm text-red-700" role="alert">Le ticket n’a pas été créé. Vérifiez les champs indiqués.</p>@endif
    <x-forms.field name="title" label="Titre" help="160 caractères maximum." :error="$errorBag->first('title')">
        <x-forms.input name="title" :value="old('title', '')" required maxlength="160" autocomplete="off" placeholder="Ex. Accès à l’application indisponible" :aria-invalid="$errorBag->has('title') ? 'true' : 'false'" :aria-describedby="$errorBag->has('title') ? 'title-help title-error' : 'title-help'" />
    </x-forms.field>
    <x-forms.field name="description" label="Description" help="Précisez l’impact et les éventuelles solutions de contournement. 5 000 caractères maximum." :error="$errorBag->first('description')">
        <x-forms.textarea name="description" :value="old('description', '')" required maxlength="5000" rows="6" placeholder="Que se passe-t-il ? Qui est concerné ?" :aria-invalid="$errorBag->has('description') ? 'true' : 'false'" :aria-describedby="$errorBag->has('description') ? 'description-help description-error' : 'description-help'" />
    </x-forms.field>
    <div class="grid gap-6 sm:grid-cols-2">
        <x-forms.field name="priority" label="Priorité choisie" :error="$errorBag->first('priority')">
            <x-forms.select name="priority" :value="old('priority', 'medium')" :options="['low' => 'Basse', 'medium' => 'Moyenne', 'high' => 'Haute']" required :aria-invalid="$errorBag->has('priority') ? 'true' : 'false'" :aria-describedby="$errorBag->has('priority') ? 'priority-error' : null" />
        </x-forms.field>
        <x-forms.field name="status" label="Statut" :error="$errorBag->first('status')">
            <x-forms.select name="status" :value="old('status', 'open')" :options="['open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu']" required :aria-invalid="$errorBag->has('status') ? 'true' : 'false'" :aria-describedby="$errorBag->has('status') ? 'status-error' : null" />
        </x-forms.field>
    </div>
    <p class="text-sm leading-6 text-muted">Votre priorité est conservée. Si un fournisseur externe est activé, seule la description lui est transmise. Évitez d’y inclure des données confidentielles.</p>
    <div class="flex flex-wrap items-center justify-end gap-3 border-t border-line pt-6">
        <x-ui.button variant="secondary" :href="route('tickets.index')">Annuler</x-ui.button>
        <x-ui.button type="submit" data-submit-button><span data-submit-label>Créer le ticket</span></x-ui.button>
    </div>
    <p data-submit-status class="text-sm text-muted" role="status" hidden>Enregistrement et analyse en cours…</p>
</form>
