<!DOCTYPE html>
<html lang="de">
<head>
    @include('partials.head', ['title' => $title ?? null, 'lockZoom' => true])
    {{-- Echte sichtbare Höhe (100dvh ist auf manchen Handys größer als die Anzeige und schneidet unten ab) --}}
    <script>
        (function () {
            var setHeight = function () {
                document.documentElement.style.setProperty('--app-height', window.innerHeight + 'px');
            };
            setHeight();
            window.addEventListener('resize', setHeight);
            window.addEventListener('orientationchange', setHeight);
        })();
    </script>
</head>
<body class="child-screen m-0 h-app overflow-hidden bg-ground font-sans text-ink antialiased">
    <main class="relative mx-auto h-app w-full max-w-[480px] overflow-hidden bg-white md:border-x-2 md:border-ink">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
