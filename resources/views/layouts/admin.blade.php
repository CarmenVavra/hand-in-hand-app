<!DOCTYPE html>
<html lang="de">
<head>
    @include('partials.head', ['title' => $title ?? null])
</head>
<body class="m-0 min-h-dvh bg-ground font-sans text-ink antialiased">
    <div class="mx-auto flex min-h-dvh w-full max-w-2xl flex-col bg-ground md:border-x-2 md:border-ink">
        <livewire:admin.nav />

        <main class="flex-1">
            {{ $slot }}
        </main>

        <footer class="flex flex-wrap items-center justify-between gap-x-6 gap-y-2 border-t-2 border-ink px-5 py-4 text-sm">
            <a href="{{ route('child.dashboard') }}" class="font-bold text-tomato hover:text-tomato-dark">◀ Zurück zum Hauptmenü</a>
            <span class="flex gap-4 text-xs">
                <a href="{{ route('legal.impressum') }}" class="underline">Impressum</a>
                <a href="{{ route('legal.privacy') }}" class="underline">Datenschutz</a>
            </span>
        </footer>
    </div>

    <x-confirm-dialog />

    @livewireScripts
</body>
</html>
