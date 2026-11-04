@props(['title' => null])
<!DOCTYPE html>
<html lang="de">
<head>
    @include('partials.head', ['title' => $title])
</head>
<body class="m-0 min-h-dvh bg-ground font-sans text-ink antialiased">
    <div class="mx-auto flex min-h-dvh w-full max-w-xl flex-col bg-white md:border-x-2 md:border-ink">
        <header class="border-b-2 border-ink bg-sun px-5 py-4">
            <a href="{{ route('child.dashboard') }}" class="text-2xl font-black tracking-tight text-ink">
                Hand<span class="text-tomato">In</span>Hand
            </a>
        </header>

        <main class="flex-1 px-5 py-6">
            {{ $slot }}
        </main>

        <footer class="flex gap-4 border-t-2 border-ink px-5 py-4 text-xs">
            <a href="{{ route('legal.impressum') }}" class="underline">Impressum</a>
            <a href="{{ route('legal.privacy') }}" class="underline">Datenschutz</a>
        </footer>
    </div>

    @livewireScripts
</body>
</html>
