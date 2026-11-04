<!DOCTYPE html>
<html lang="de">
<head>
    @include('partials.head', ['title' => $title ?? null, 'lockZoom' => true])
</head>
<body class="child-screen m-0 h-dvh overflow-hidden bg-ground font-sans text-ink antialiased">
    <main class="relative mx-auto h-dvh w-full max-w-[480px] overflow-hidden bg-white md:border-x-2 md:border-ink">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
