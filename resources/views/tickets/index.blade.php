<x-layouts.app title="Tickets">
    <div class="mb-8 flex items-end justify-between gap-4">
        <div><h1 class="mb-2 text-3xl font-bold tracking-tight">Tickets</h1><p class="max-w-prose text-sm leading-6 text-muted">Consultez et traitez les demandes de support interne.</p></div>
        <p class="shrink-0 pb-1 text-sm font-semibold text-muted">{{ $tickets->total() }} {{ $tickets->total() === 1 ? 'ticket' : 'tickets' }}</p>
    </div>
    <section class="overflow-hidden rounded-xl border border-line bg-white" aria-label="File des tickets">
        <form method="GET" action="{{ route('tickets.index') }}" class="flex flex-wrap items-end gap-4 border-b border-line p-5 sm:p-6">
            <x-forms.field name="status" label="Statut" class="w-full sm:w-44">
                <x-forms.select name="status" :value="$filters['status']" :options="['' => 'Tous', 'open' => 'Ouvert', 'in_progress' => 'En cours', 'resolved' => 'Résolu']" />
            </x-forms.field>
            <x-forms.field name="priority" label="Priorité" class="w-full sm:w-44">
                <x-forms.select name="priority" :value="$filters['priority']" :options="['' => 'Toutes', 'low' => 'Basse', 'medium' => 'Moyenne', 'high' => 'Haute']" />
            </x-forms.field>
            <x-ui.button type="submit" variant="secondary">Appliquer</x-ui.button>
            <a href="{{ route('tickets.index') }}" class="inline-flex min-h-11 items-center gap-2 text-sm font-semibold text-muted hover:text-brand sm:ml-auto"><span aria-hidden="true">↻</span>Réinitialiser les filtres</a>
        </form>
        @if($tickets->isEmpty())
            @if($filters['status'] !== '' || $filters['priority'] !== '' || $tickets->currentPage() > 1)
                <x-ui.empty-state title="Aucun résultat" description="Aucun ticket ne correspond à cette sélection ou à cette page.">
                    @if($tickets->currentPage() > 1)
                        <x-ui.button variant="secondary" :href="route('tickets.index', array_filter($filters))">Revenir à la première page</x-ui.button>
                    @else
                        <x-ui.button variant="secondary" :href="route('tickets.index')">Réinitialiser les filtres</x-ui.button>
                    @endif
                </x-ui.empty-state>
            @else
                <x-ui.empty-state title="Aucun ticket pour le moment" description="Créez une demande pour commencer votre file de support.">
                    <x-ui.button :href="route('tickets.create')" data-panel-link>Créer un ticket</x-ui.button>
                </x-ui.empty-state>
            @endif
        @else
            <div class="hidden lg:block">
                <table class="w-full table-fixed text-left text-sm">
                    <caption class="sr-only">Tickets de support, triés du plus récent au plus ancien</caption>
                    <thead class="border-b border-line bg-slate-50 text-xs text-muted"><tr>
                        <th scope="col" class="w-24 px-5 py-4 font-semibold">Référence</th>
                        <th scope="col" class="px-4 py-4 font-semibold">Titre</th>
                        <th scope="col" class="w-28 px-3 py-4 font-semibold">Priorité</th>
                        <th scope="col" class="w-28 px-3 py-4 font-semibold">Statut</th>
                        <th scope="col" class="w-44 px-5 py-4 font-semibold">Date de création</th>
                    </tr></thead>
                    <tbody class="divide-y divide-line">
                    @foreach($tickets as $ticket)
                        <tr class="hover:bg-selected/50">
                            <td class="px-5 py-6 text-xs font-semibold text-muted">{{ $ticket->reference }}</td>
                            <td class="px-4 py-6">
                                <a href="{{ route('tickets.show', $ticket->id) }}" data-panel-link aria-label="Ouvrir le ticket {{ $ticket->reference }} : {{ $ticket->title }}" class="mb-1 block truncate font-semibold hover:text-brand">{{ $ticket->title }}</a>
                                <p class="truncate text-xs leading-5 text-muted">{{ $ticket->description }}</p>
                            </td>
                            <td class="px-3 py-6"><x-tickets.priority-badge :priority="$ticket->priority" /></td>
                            <td class="px-3 py-6"><x-tickets.status-badge :status="$ticket->status" /></td>
                            <td class="px-5 py-6 text-xs text-muted">{{ $ticket->created_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y') }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <ul class="divide-y divide-line lg:hidden" aria-label="Tickets">
                @foreach($tickets as $ticket)
                    <li class="space-y-3 p-5">
                        <div class="flex items-center justify-between gap-3 text-xs text-muted"><span class="font-semibold">{{ $ticket->reference }}</span><time datetime="{{ $ticket->created_at->toIso8601String() }}">{{ $ticket->created_at->timezone('Africa/Casablanca')->locale('fr')->translatedFormat('j F Y') }}</time></div>
                        <a href="{{ route('tickets.show', $ticket->id) }}" data-panel-link aria-label="Ouvrir le ticket {{ $ticket->reference }} : {{ $ticket->title }}" class="block break-words font-semibold hover:text-brand">{{ $ticket->title }}</a>
                        <p class="line-clamp-2 break-words text-sm leading-6 text-muted">{{ $ticket->description }}</p>
                        <div class="flex flex-wrap gap-2"><x-tickets.priority-badge :priority="$ticket->priority" /><x-tickets.status-badge :status="$ticket->status" /></div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
    @if($tickets->hasPages())<div class="mt-6">{{ $tickets->links() }}</div>@endif
</x-layouts.app>
