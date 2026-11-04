<div class="relative h-full" x-data="mailbox({ items: @js($items) })">

    {{-- Kachel-Übersicht --}}
    <div class="flex h-full flex-col bg-cobalt" x-bind:inert="open">
        <header class="flex flex-none items-center gap-4 border-b-2 border-ink bg-sun px-5 py-3">
            <a href="{{ route('child.dashboard') }}" aria-label="Zum Hauptmenü"
               class="flex size-16 flex-none items-center justify-center border-2 border-ink bg-white">
                <x-icon name="home" class="size-8.5" stroke-width="2.2" />
            </a>
            <h1 class="text-[30px] leading-none font-black">Postkasten</h1>
        </header>

        <div class="grid min-h-0 flex-1 auto-rows-[minmax(8rem,1fr)] grid-cols-2 gap-0.5 overflow-y-auto bg-ink pt-0.5">
            @forelse ($items as $index => $item)
                <button type="button" x-on:click="openItem({{ $index }})"
                        class="relative flex flex-col justify-end p-3.5 text-left text-white"
                        style="background-color: {{ $item['color'] }}"
                        aria-label="{{ $item['title'] }} abspielen">
                    <span x-show="isNew(items[{{ $index }}])" @if (! $item['isNew']) x-cloak @endif
                          class="absolute top-3 left-3 animate-blink bg-sun px-2.5 py-1 text-sm font-black text-ink">NEU</span>
                    <x-icon name="{{ $item['isAudio'] ? 'speaker' : 'play' }}"
                            class="absolute top-1/2 left-1/2 size-18 -translate-x-1/2 -translate-y-1/2" />
                    <span class="text-lg leading-tight font-extrabold">{{ $item['title'] }}</span>
                </button>
            @empty
                <div class="col-span-2 flex flex-col items-center justify-center gap-4 bg-cobalt p-8 text-center text-white">
                    <x-icon name="mail" class="size-24" />
                    <p class="text-xl font-extrabold">Noch keine Post da.</p>
                </div>
            @endforelse
        </div>

        <a href="{{ route('child.record') }}"
           class="flex min-h-20 flex-none items-center gap-4 border-t-2 border-ink bg-tomato px-5 pb-[max(env(safe-area-inset-bottom),0.5rem)] text-white active:bg-tomato-dark">
            <x-icon name="video" class="size-11" />
            <span class="text-[26px] font-black">{{ $sendLabel }}</span>
        </a>
    </div>

    {{-- Vollbild-Player --}}
    <div x-show="open" x-cloak
         class="absolute inset-0 z-10 flex flex-col text-white"
         x-bind:style="current ? 'background-color:' + current.color : ''"
         role="dialog" aria-modal="true" aria-label="Video ansehen">

        <div class="relative flex min-h-0 flex-1 items-center justify-center" x-on:click="toggle()">
            <video x-ref="video" playsinline
                   class="size-full object-contain"
                   x-on:timeupdate="onTime()" x-on:ended="onEnded()"
                   x-on:play="paused = false" x-on:pause="paused = true"></video>

            <x-icon name="speaker" class="absolute size-32 opacity-90" x-show="current?.isAudio" x-cloak />

            <x-icon name="play" class="pointer-events-none absolute size-30 opacity-95" x-show="paused" x-cloak />

            <p class="pointer-events-none absolute top-5 left-5 text-2xl font-black drop-shadow" x-text="current?.title"></p>
        </div>

        <div class="h-3.5 flex-none bg-black/35" role="progressbar" aria-label="Fortschritt"
             x-bind:aria-valuenow="Math.round(progress)" aria-valuemin="0" aria-valuemax="100">
            <div class="h-full bg-sun" x-bind:style="'width:' + progress + '%'"></div>
        </div>

        <div class="flex h-30 flex-none items-center justify-between bg-ink px-6">
            <button type="button" x-on:click="close()" aria-label="Zurück zum Postkasten"
                    class="flex size-21 items-center justify-center border-2 border-white bg-ink text-white">
                <x-icon name="home" class="size-11" stroke-width="2.2" />
            </button>
            <button type="button" x-on:click="replay()" aria-label="Nochmal ansehen"
                    class="flex size-21 items-center justify-center bg-sun text-ink">
                <x-icon name="replay" class="size-12" stroke-width="2.4" />
            </button>
        </div>
    </div>
</div>
