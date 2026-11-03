<x-layouts.app :title="$ticket->reference">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('tickets.index') }}" class="mb-6 inline-flex min-h-11 items-center text-sm font-semibold text-muted">← Retour à la file</a>
        <x-tickets.detail :ticket="$ticket" />
    </div>
</x-layouts.app>
