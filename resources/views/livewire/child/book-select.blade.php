<div class="flex h-full flex-col bg-leaf">
    <header class="flex flex-none items-center gap-4 border-b-2 border-ink bg-sun px-5 py-4">
        <a href="{{ route('child.dashboard') }}" aria-label="Zum Hauptmenü"
           class="flex size-16 flex-none items-center justify-center border-2 border-ink bg-white">
            <x-icon name="home" class="size-8.5" stroke-width="2.2" />
        </a>
        <h1 class="text-[30px] leading-none font-black">Bilderbuch</h1>
    </header>

    <div class="grid min-h-0 flex-1 auto-rows-[minmax(9rem,1fr)] grid-cols-1 gap-0.5 overflow-y-auto bg-ink pt-0.5">
        @forelse ($books as $book)
            <a href="{{ route('child.book.view', $book['id']) }}"
               class="relative flex items-end overflow-hidden p-4 text-white"
               style="background-color: {{ $book['color'] }}">
                @if ($book['cover'])
                    <img src="{{ $book['cover'] }}" alt="" class="absolute inset-0 size-full object-cover">
                    <span class="absolute inset-x-0 bottom-0 h-1/2 bg-linear-to-t from-ink/70 to-transparent"></span>
                @endif
                <span class="relative text-3xl leading-tight font-black drop-shadow">{{ $book['title'] }}</span>
            </a>
        @empty
            <div class="flex flex-col items-center justify-center gap-4 bg-leaf p-8 text-center text-white">
                <x-icon name="book" class="size-24" />
                <p class="text-xl font-extrabold">Hier ist noch kein Bild drin!</p>
            </div>
        @endforelse
    </div>
</div>
