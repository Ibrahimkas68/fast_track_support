@props(['ticket'])
<article class="space-y-6">
    <div class="rounded-xl border border-line bg-white p-5 sm:p-8">
        <p class="mb-3 text-sm font-semibold text-muted">{{ $ticket->reference }}</p>
        <h1 class="mb-6 break-words text-2xl font-bold tracking-tight">{{ $ticket->title }}</h1>
        <dl class="mb-6 flex flex-wrap gap-x-8 gap-y-4 border-b border-line pb-6 text-sm">
            <div><dt class="mb-2 text-muted">Priorité choisie</dt><dd><x-tickets.priority-badge :priority="$ticket->priority" /></dd></div>
            <div><dt class="mb-2 text-muted">Statut</dt><dd><x-tickets.status-badge :status="$ticket->status" /></dd></div>
            <div><dt class="mb-2 text-muted">Date de création</dt><dd>{{ $ticket->created_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y, H:i') }}</dd></div>
        </dl>
        <h2 class="mb-3 font-semibold">Description</h2>
        <p class="whitespace-pre-wrap break-words text-sm leading-7 text-muted">{{ $ticket->description }}</p>
    </div>
    <x-tickets.urgency-analysis :priority="$ticket->suggested_priority" :reason="$ticket->analysis_reason" :source="$ticket->analysis_source" />
</article>
