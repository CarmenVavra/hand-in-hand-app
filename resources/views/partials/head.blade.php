<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover{{ ($lockZoom ?? false) ? ', maximum-scale=1, user-scalable=no' : '' }}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="robots" content="noindex, nofollow">
<title>{{ ! empty($title) ? $title.' · ' : '' }}{{ config('app.name') }}</title>

{{-- PWA --}}
<meta name="theme-color" content="#FFD23F">
<link rel="manifest" href="/manifest.webmanifest">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="HandInHand">
<link rel="icon" type="image/png" href="/app-icons/icon-192.png">
<link rel="apple-touch-icon" href="/app-icons/icon-192.png">

@vite(['resources/css/app.css', 'resources/js/app.js'])
@livewireStyles
