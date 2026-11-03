@props(['title' => 'Tickets'])
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} | Fast Track Support</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-ink antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:z-50 focus:bg-white focus:p-4">Aller au contenu</a>
<header class="border-b border-line bg-white">
    <div class="mx-auto flex min-h-20 max-w-[1280px] items-center justify-between gap-4 px-5 sm:px-8">
        <a href="{{ route('tickets.index') }}" class="flex min-w-0 items-center gap-3 rounded-lg" aria-label="Fast Track Support, accueil">
            <span class="flex size-10 shrink-0 items-end justify-center gap-1 rounded-xl bg-brand p-2.5" aria-hidden="true"><span class="h-2 w-1 rounded-full bg-white"></span><span class="h-5 w-1 rounded-full bg-white"></span><span class="h-3 w-1 rounded-full bg-white"></span></span>
            <span><span class="block text-sm font-bold sm:text-base">Fast Track Support</span><span class="block text-xs text-muted sm:text-sm">Support interne</span></span>
        </a>
        <x-ui.button :href="route('tickets.create')" data-panel-link><span aria-hidden="true">＋</span><span class="hidden sm:inline">Créer un ticket</span><span class="sm:hidden">Créer</span></x-ui.button>
    </div>
</header>
<main id="main" class="mx-auto max-w-[1280px] px-5 py-8 sm:px-8 sm:py-12">
    @if(session('success'))
        <x-ui.alert variant="success" class="mb-6">{{ session('success') }}</x-ui.alert>
    @endif
    {{ $slot }}
</main>
<x-ui.panel id="ticket-panel" title="Ticket" />
</body>
</html>
