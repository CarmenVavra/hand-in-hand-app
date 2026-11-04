<div class="relative h-full overflow-hidden text-white" wire:ignore
     x-data="bookViewer({ pages: @js($pages) })"
     x-bind:style="page ? 'background-color:' + page.color : 'background-color:#0e8f55'">

    <audio x-ref="audio" preload="auto"></audio>

    {{-- Keine Seiten --}}
    <template x-if="pages.length === 0">
        <div class="flex h-full flex-col items-center justify-center gap-6 bg-leaf p-8 text-center">
            <x-icon name="book" class="size-24" />
            <p class="text-2xl font-extrabold">Hier ist noch kein Bild drin!</p>
        </div>
    </template>

    <template x-if="pages.length > 0">
        <div class="absolute inset-0">
            {{-- Foto: ganze Fläche über der Fußleiste --}}
            <div class="absolute inset-x-0 top-0 bottom-30 flex items-center justify-center">
                <img x-bind:src="page.image" x-bind:alt="page.label || 'Bilderbuch-Seite ' + (index + 1)"
                     class="size-full object-contain" draggable="false">
            </div>

            {{-- Zurück: linke 25 % --}}
            <div class="absolute top-0 left-0 flex h-full w-1/4 items-center pl-2.5" x-on:click="prev()"
                 role="button" tabindex="0" aria-label="Eine Seite zurück"
                 x-on:keydown.enter.prevent="prev()" x-on:keydown.left.window="prev()">
                <x-icon name="chevron-left" class="size-12" x-show="!isFirst" x-cloak />
            </div>

            {{-- Weiter: rechte 75 % --}}
            <div class="absolute top-0 right-0 flex h-full w-3/4 items-center justify-end pr-2.5" x-on:click="next()"
                 role="button" tabindex="0" aria-label="Eine Seite weiter"
                 x-on:keydown.enter.prevent="next()" x-on:keydown.right.window="next()">
                <x-icon name="chevron-right" class="size-16" x-show="!isLast" x-cloak />
            </div>

            {{-- Autoplay blockiert: Tippen startet die Stimme --}}
            <div x-show="blocked" x-cloak class="pointer-events-none absolute inset-x-0 top-1/3 flex justify-center">
                <x-icon name="play" class="size-32 animate-pulse-big drop-shadow-lg" />
            </div>

            {{-- Fußleiste: Stimme (Tippen = nochmal hören) --}}
            <button type="button" x-on:click="replay()" aria-label="Stimme nochmal hören"
                    class="absolute inset-x-0 bottom-0 flex h-30 items-center gap-1.5 border-t-2 border-white bg-ink px-6 pb-[max(env(safe-area-inset-bottom),0.5rem)]">
                <x-icon name="speaker" class="size-10 flex-none text-sun" />
                <span class="ml-2 flex h-[50px] items-center gap-[5px]" aria-hidden="true">
                    @foreach (range(0, 13) as $bar)
                        <span class="h-2.5 w-[7px] shrink-0 animate-wave bg-sun"
                              x-bind:style="'animation-delay: -{{ ($bar % 5) * 0.18 }}s; animation-play-state:' + (playing ? 'running' : 'paused')"></span>
                    @endforeach
                </span>
                <span class="ml-auto text-base font-extrabold" x-text="pageNumber"></span>
            </button>
        </div>
    </template>

    {{-- Home oben links --}}
    <a href="{{ route('child.dashboard') }}" aria-label="Zum Hauptmenü"
       class="absolute top-4 left-4 z-10 flex size-16 items-center justify-center border-2 border-ink bg-sun text-ink">
        <x-icon name="home" class="size-8.5" stroke-width="2.2" />
    </a>

    {{-- Letzte Seite: großer Home-Button --}}
    <a href="{{ route('child.dashboard') }}" aria-label="Fertig – zum Hauptmenü"
       x-show="pages.length > 0 && isLast" x-cloak
       class="absolute top-60 right-5 z-10 flex size-38 animate-pulse-slow items-center justify-center border-4 border-ink bg-sun text-ink">
        <x-icon name="home" class="size-22" />
    </a>
</div>
