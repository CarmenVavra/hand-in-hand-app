@props(['name'])
@php
    // Lucide-Stil, wie im Mockup: eckige Linien, 2px Strich. "play" und "speaker" sind gefüllt.
    $filled = in_array($name, ['play', 'speaker'], true);
@endphp
<svg {{ $attributes->merge([
    'viewBox' => '0 0 24 24',
    'aria-hidden' => 'true',
    'fill' => $filled ? 'currentColor' : 'none',
    'stroke' => $filled ? 'none' : 'currentColor',
    'stroke-width' => '2',
    'stroke-linejoin' => 'round',
]) }}>
    @switch($name)
        @case('video')
            <rect x="2" y="6" width="14" height="12"/><path d="M16 10l6-3v10l-6-3z"/>
            @break
        @case('mail')
            <rect x="2" y="5" width="20" height="14"/><path d="M2 6l10 8 10-8"/>
            @break
        @case('book')
            <path d="M12 6c-2-2-5-2-9-2v14c4 0 7 0 9 2 2-2 5-2 9-2V4c-4 0-7 0-9 2z"/><path d="M12 6v14"/>
            @break
        @case('home')
            <path d="M3 11l9-8 9 8v10H3z"/>
            @break
        @case('replay')
            <path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>
            @break
        @case('play')
            <path d="M7 4l13 8-13 8z"/>
            @break
        @case('speaker')
            <path d="M4 9v6h4l5 4V5L8 9z"/>
            @break
        @case('mic')
            <rect x="9" y="3" width="6" height="12"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/>
            @break
        @case('chevron-left')
            <path d="M15 4l-8 8 8 8" stroke-width="3"/>
            @break
        @case('chevron-right')
            <path d="M9 4l8 8-8 8" stroke-width="3"/>
            @break
        @case('check')
            <path d="M4 12l6 6L20 6" stroke-width="3"/>
            @break
        @case('camera')
            <path d="M3 7h4l2-3h6l2 3h4v13H3z"/><circle cx="12" cy="13" r="4"/>
            @break
    @endswitch
</svg>
