<div>
    <div class="border-b-2 border-ink px-5 py-5">
        <h1 class="text-[26px] leading-tight font-black">Bilderbücher</h1>
        <p class="mt-0.5 text-sm">{{ $books->count() }} {{ $books->count() === 1 ? 'Buch' : 'Bücher' }}</p>
    </div>

    <x-toast :message="$toast" />

    <ul class="bg-white">
        @forelse ($books as $book)
            <li class="border-b-2 border-ink">
                <a href="{{ route('admin.book.show', $book) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-sun/40">
                    <span class="flex size-15 flex-none items-center justify-center overflow-hidden bg-ink text-sun">
                        @if ($book->coverUrl())
                            <img src="{{ $book->coverUrl() }}" alt="" loading="lazy" class="size-full object-cover">
                        @else
                            <x-icon name="book" class="size-7" />
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-[15px] font-extrabold">{{ $book->title }}</span>
                        <span class="block text-xs">{{ $book->pages_count }} {{ $book->pages_count === 1 ? 'Seite' : 'Seiten' }}</span>
                    </span>
                    <span class="text-xl font-black" aria-hidden="true">›</span>
                </a>
            </li>
        @empty
            <li class="border-b-2 border-ink px-5 py-6 text-sm">Noch kein Bilderbuch. Lege unten das erste an.</li>
        @endforelse
    </ul>

    <form wire:submit="createBook" class="space-y-3 px-5 py-6">
        <label for="title" class="field-label">Neues Bilderbuch</label>
        <div class="flex gap-2">
            <input id="title" type="text" wire:model="title" maxlength="80" placeholder="z. B. Tiere im Garten" class="field">
            <button type="submit" class="btn btn-primary flex-none" wire:loading.attr="disabled">+ Neu</button>
        </div>
        @error('title') <p class="text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
    </form>
</div>
