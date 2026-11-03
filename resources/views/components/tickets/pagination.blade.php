@props(['paginator'])
<nav aria-label="Pagination des tickets" class="flex flex-wrap items-center justify-between gap-3 text-sm">
    @if($paginator->onFirstPage())
        <x-ui.button variant="secondary" disabled>Précédent</x-ui.button>
    @else
        <x-ui.button variant="secondary" :href="$paginator->previousPageUrl()" rel="prev">Précédent</x-ui.button>
    @endif
    <p class="text-muted">Page {{ $paginator->currentPage() }} sur {{ $paginator->lastPage() }}</p>
    @if($paginator->hasMorePages())
        <x-ui.button variant="secondary" :href="$paginator->nextPageUrl()" rel="next">Suivant</x-ui.button>
    @else
        <x-ui.button variant="secondary" disabled>Suivant</x-ui.button>
    @endif
</nav>
