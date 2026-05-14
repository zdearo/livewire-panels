@php
    $currentPanel = \Zdearo\LivewirePanels\Facades\LivewirePanels::currentPanel();
    $vite = $currentPanel?->vite ?? [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-livewire-panels-layout="app">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

        @if ($vite !== [])
            @vite($vite)
        @endif

        @fluxAppearance
        @stack('styles')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased" data-livewire-panels-body>
        {{ $slot }}

        @livewireScripts
        @fluxScripts
        @stack('scripts')
    </body>
</html>
