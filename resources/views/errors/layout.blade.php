<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Fast Track Support</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto max-w-xl px-6 py-20">
        <p class="mb-4 text-sm font-semibold text-blue-700">Fast Track Support</p>
        <h1 class="mb-4 text-3xl font-bold">@yield('title')</h1>
        <p class="mb-8 text-slate-600">@yield('message')</p>
        <a href="@yield('link')" class="inline-flex min-h-11 items-center rounded-lg bg-blue-700 px-5 py-3 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700">@yield('action')</a>
    </main>
</body>
</html>
