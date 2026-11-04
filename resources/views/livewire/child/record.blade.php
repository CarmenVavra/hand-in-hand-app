<div class="flex h-full flex-col bg-ink"
     x-data="videoRecorder({ maxSeconds: {{ $maxSeconds }}, homeUrl: @js(route('child.dashboard')) })">

    {{-- Kamera-Vorschau --}}
    <div class="relative flex min-h-0 flex-1 items-center justify-center bg-tomato text-white">
        <video x-ref="preview" autoplay muted playsinline
               class="absolute inset-0 size-full -scale-x-100 bg-[#7a1a0a] object-cover"
               aria-label="Vorschau der Selfie-Kamera"></video>

        {{-- Zeit-Anzeige --}}
        <div x-show="recording" x-cloak
             class="absolute top-4 left-4 flex items-center gap-2.5 bg-white px-3 py-1.5 text-base font-extrabold text-ink"
             role="timer" aria-live="off">
            <span class="size-3.5 animate-blink bg-tomato" aria-hidden="true"></span>
            <span x-text="clock"></span>
        </div>

        {{-- Fehler / nicht unterstützt --}}
        <p x-show="problem" x-cloak x-text="problemText" role="alert"
           class="relative max-w-xs bg-ink/90 p-5 text-xl leading-snug font-extrabold"></p>

        {{-- Wird gesendet --}}
        <p x-show="state === 'sending'" x-cloak role="status"
           class="relative bg-ink/80 px-5 py-3 text-2xl font-black">Wird gesendet …</p>

        {{-- Gesendet! --}}
        <div x-show="state === 'sent'" x-cloak role="status"
             class="absolute inset-0 flex flex-col justify-center bg-tomato p-8">
            <p class="text-6xl leading-none font-black">Gesendet!</p>
            <p class="mt-3 text-xl leading-snug font-semibold">{{ $auntName }} bekommt dein Video.</p>
        </div>
    </div>

    {{-- Bedienleiste --}}
    <div class="flex h-60 max-h-[34%] flex-none items-center justify-between border-t-2 border-white bg-sun px-7 pb-[max(env(safe-area-inset-bottom),0.5rem)]">
        <a href="{{ route('child.dashboard') }}" aria-label="Zum Hauptmenü"
           class="flex size-21 items-center justify-center border-2 border-ink bg-white text-ink">
            <x-icon name="home" class="size-11" stroke-width="2.2" />
        </a>

        <button type="button" x-on:click="toggle()"
                x-bind:class="recording ? 'animate-pulse-big' : ''"
                x-bind:aria-label="recording ? 'Aufnahme beenden und senden' : 'Aufnahme starten'"
                x-bind:disabled="state === 'sending' || state === 'sent' || state === 'init'"
                class="flex size-32 items-center justify-center rounded-full border-[6px] border-ink bg-tomato disabled:opacity-60">
            <span x-show="recording" x-cloak class="size-11 bg-white"></span>
            <span x-show="!recording" class="size-11 rounded-full bg-white"></span>
        </button>

        <span class="w-21" aria-hidden="true"></span>
    </div>
</div>
