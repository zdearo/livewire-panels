@props([
    'alias',
])

@php
    $icon = app(\Zdearo\LivewirePanels\Icons\PanelsIconManager::class)->resolve($alias);
@endphp

@if(is_string($icon))
    <x-icon :name="$icon" {{ $attributes }} />
@else
    {!! $icon->toHtml() !!}
@endif
