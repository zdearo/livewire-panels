@php
$currentPanel = \Zdearo\LivewirePanels\Facades\Panels::currentPanel();
$vite = $currentPanel?->vite ?? [];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-livewire-panels-layout="app">

<head>
   <meta charset="utf-8">
   <meta name="viewport" content="width=device-width, initial-scale=1">

   <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

   @if ($vite !== [])
   @vite($vite)
   @endif

   @fluxAppearance
   @stack('styles')
</head>

<body data-livewire-panels-body>
   {{ $slot }}

   @livewireScripts
   @fluxScripts
   @stack('scripts')
</body>

</html>