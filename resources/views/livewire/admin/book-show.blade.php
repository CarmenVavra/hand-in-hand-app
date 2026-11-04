<div>
    <div class="flex items-end justify-between gap-3 border-b-2 border-ink px-5 py-5">
        <div class="min-w-0">
            <h1 class="truncate text-[26px] leading-tight font-black">{{ $book->title }}</h1>
            <p class="mt-0.5 text-sm">{{ $book->pages->count() }} {{ $book->pages->count() === 1 ? 'Seite' : 'Seiten' }}</p>
        </div>
        <a href="{{ route('admin.page.create', $book) }}" class="btn btn-primary flex-none">+ Neu</a>
    </div>

    <x-toast :message="$toast" />

    <ul class="bg-white">
        @forelse ($book->pages as $page)
            <li wire:key="page-{{ $page->id }}" class="flex items-center gap-3 border-b-2 border-ink px-4 py-3">
                <img src="{{ $page->imageUrl() }}" alt="Foto von Seite {{ $loop->iteration }}" loading="lazy"
                     class="size-15 flex-none object-cover"
                     style="background-color: {{ $colors[$loop->index % count($colors)] }}">

                <div class="min-w-0 flex-1">
                    <p class="truncate text-[15px] font-extrabold">Seite {{ $loop->iteration }}@if ($page->label) · {{ $page->label }}@endif</p>
                    <audio src="{{ $page->audioUrl() }}" controls preload="none" class="mt-1 h-9 w-full"
                           aria-label="Stimme zu Seite {{ $loop->iteration }}"></audio>
                </div>

                <div class="flex flex-none flex-col gap-1">
                    <button type="button" wire:click="moveUp({{ $page->id }})" @disabled($loop->first)
                            aria-label="Seite {{ $loop->iteration }} nach oben"
                            class="h-8 w-9 border-2 border-ink bg-white text-sm disabled:opacity-25">▲</button>
                    <button type="button" aria-label="Seite {{ $loop->iteration }} löschen"
                            x-data
                            x-on:click="$dispatch('confirm-dialog', {
                                title: 'Seite löschen?',
                                message: 'Seite {{ $loop->iteration }} wird mit Foto und Stimme endgültig gelöscht.',
                                confirmLabel: 'Löschen',
                                onConfirm: () => $wire.deletePage({{ $page->id }}),
                            })"
                            class="h-8 w-9 border-2 border-ink bg-tomato font-black text-white hover:bg-tomato-dark">✕</button>
                    <button type="button" wire:click="moveDown({{ $page->id }})" @disabled($loop->last)
                            aria-label="Seite {{ $loop->iteration }} nach unten"
                            class="h-8 w-9 border-2 border-ink bg-white text-sm disabled:opacity-25">▼</button>
                </div>
            </li>
        @empty
            <li class="border-b-2 border-ink px-5 py-6 text-sm">Dieses Buch hat noch keine Seiten. Tippe auf „+ Neu“.</li>
        @endforelse
    </ul>

    <details class="border-b-2 border-ink px-5 py-4">
        <summary class="min-h-11 py-2 text-sm font-extrabold">Buch bearbeiten</summary>

        <form wire:submit="rename" class="mt-3 space-y-2">
            <label for="book-title" class="field-label">Titel</label>
            <div class="flex gap-2">
                <input id="book-title" type="text" wire:model="title" maxlength="80" class="field">
                <button type="submit" class="btn btn-plain flex-none">Speichern</button>
            </div>
            @error('title') <p class="text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </form>

        <button type="button" x-data
                x-on:click="$dispatch('confirm-dialog', {
                    title: 'Ganzes Buch löschen?',
                    message: @js('„'.$book->title.'“ wird mit allen Seiten, Fotos und Stimmen endgültig gelöscht.'),
                    confirmLabel: 'Buch löschen',
                    onConfirm: () => $wire.deleteBook(),
                })"
                class="btn btn-primary mt-5">Buch löschen</button>
    </details>
</div>
