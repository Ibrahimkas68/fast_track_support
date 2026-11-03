<x-layouts.app title="Créer un ticket">
    <div class="mx-auto max-w-2xl">
        <a href="{{ route('tickets.index') }}" class="mb-6 inline-flex min-h-11 items-center text-sm font-semibold text-muted">← Retour à la file</a>
        <h1 class="mb-2 text-3xl font-bold tracking-tight">Créer un ticket</h1>
        <p class="mb-8 text-sm leading-6 text-muted">Décrivez la demande. Une analyse d’urgence vous sera proposée sans modifier votre priorité.</p>
        <div class="rounded-xl border border-line bg-white p-5 sm:p-8"><x-tickets.form :action="route('tickets.store')" :error-bag="$formErrors" /></div>
    </div>
</x-layouts.app>
