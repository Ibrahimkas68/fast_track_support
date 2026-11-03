@props(['priority', 'reason', 'source'])
<section class="rounded-xl border border-blue-200 bg-blue-50/60 p-5" aria-label="Analyse d’urgence">
    <h2 class="mb-4 font-semibold">Urgence recommandée</h2>
    <x-tickets.priority-badge :priority="$priority" />
    <p class="mt-3 whitespace-pre-wrap break-words text-sm leading-6">{{ $reason }}</p>
    <p class="mt-4 text-xs font-semibold text-muted">{{ $source === 'llm' ? 'Analyse par modèle de langage' : 'Analyse locale de secours' }}</p>
    @if($source === 'local')<p class="mt-2 text-sm leading-6 text-muted">Le service d’analyse est indisponible ou non configuré. Une analyse locale a été utilisée.</p>@endif
    <p class="mt-3 text-xs text-muted">Cette recommandation ne modifie pas la priorité choisie.</p>
</section>
