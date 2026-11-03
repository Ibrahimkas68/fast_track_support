@props(['id', 'title'])
<dialog id="{{ $id }}" aria-labelledby="{{ $id }}-title" class="m-auto max-h-[90dvh] w-[calc(100%-2rem)] max-w-2xl overflow-y-auto rounded-2xl border border-line bg-white p-0 text-ink shadow-xl backdrop:bg-slate-950/35">
    <div class="flex items-center justify-between gap-4 border-b border-line px-6 py-4">
        <h2 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h2>
        <button type="button" data-panel-close aria-label="Fermer le panneau" class="flex size-11 items-center justify-center rounded-lg text-2xl text-muted hover:bg-slate-100">×</button>
    </div>
    <div data-panel-content class="p-6" aria-live="polite"></div>
</dialog>
