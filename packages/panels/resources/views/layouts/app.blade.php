<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-livewire-panels-layout="app">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        @stack('styles')
    </head>
    <body>
        {{ $slot }}

        @stack('scripts')
    </body>
</html>
