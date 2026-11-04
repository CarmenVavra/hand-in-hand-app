<div x-data="audioRecorder" class="flex flex-col">
    <div class="border-b-2 border-ink px-5 py-5">
        <p class="eyebrow">{{ $book->title }}</p>
        <h1 class="mt-1 text-[26px] leading-tight font-black">Neue Bilderbuch-Seite</h1>
    </div>

    <form wire:submit="save" class="flex flex-col">
        <div class="space-y-5 p-5">
            {{-- 1 · Foto --}}
            <div x-data="{ uploading: false, failed: false, progress: 0 }"
                 x-on:livewire-upload-start="uploading = true; failed = false; progress = 0"
                 x-on:livewire-upload-finish="uploading = false"
                 x-on:livewire-upload-error="uploading = false; failed = true"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <span class="field-label" id="photo-label">1 · Foto</span>

                <label for="photo"
                       class="relative flex h-[170px] w-full cursor-pointer items-end overflow-hidden border-2 border-ink p-3.5 text-left text-[15px] font-extrabold focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-tomato
                              {{ $photo ? 'bg-sun' : 'bg-white' }}">
                    @if ($photoPreview)
                        <img src="{{ $photoPreview }}" alt="Vorschau des gewählten Fotos" class="absolute inset-0 size-full object-cover">
                    @endif
                    <span class="relative flex items-center gap-2 {{ $photoPreview ? 'bg-sun px-2 py-1' : '' }}">
                        @if ($photo)
                            <x-icon name="check" class="size-5" /> Foto gewählt – tippen zum Ändern
                        @else
                            <x-icon name="camera" class="size-6" /> + Foto auswählen
                        @endif
                    </span>
                </label>
                <input id="photo" type="file" wire:model="photo" accept="image/*" class="sr-only" aria-labelledby="photo-label">

                <div x-show="uploading" x-cloak class="mt-2 h-2 bg-line" role="progressbar" aria-label="Foto wird hochgeladen">
                    <div class="h-full bg-ink" x-bind:style="'width:' + progress + '%'"></div>
                </div>
                <p x-show="failed" x-cloak class="mt-2 text-sm font-bold text-tomato" role="alert">
                    Der Upload hat nicht geklappt – das Foto ist vermutlich zu groß. Bitte ein kleineres wählen.
                </p>
                @error('photo') <p class="mt-2 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
            </div>

            {{-- 2 · Stimme --}}
            <div>
                <span class="field-label">2 · Deine Stimme</span>
                <div class="border-2 border-ink bg-white p-3.5">
                    <div class="flex items-center gap-4">
                        <button type="button" x-on:click="toggle()"
                                x-bind:aria-label="recState === 1 ? 'Aufnahme beenden' : (recState === 2 ? 'Neu aufnehmen' : 'Aufnahme starten')"
                                x-bind:class="recState === 1 ? 'animate-pulse-big' : ''"
                                class="flex size-18 flex-none items-center justify-center rounded-full border-[3px] border-ink bg-tomato text-white">
                            <span x-show="recState === 1" x-cloak class="size-6.5 bg-white"></span>
                            <x-icon name="mic" class="size-8.5" x-show="recState !== 1" x-cloak stroke-width="2.4" />
                        </button>
                        <p class="text-sm leading-snug font-bold" role="status" aria-live="polite" x-text="statusText"></p>
                    </div>

                    <audio x-show="audioUrl" x-cloak x-bind:src="audioUrl" controls class="mt-3 h-10 w-full"
                           aria-label="Aufnahme anhören"></audio>
                </div>
                @error('audio') <p class="mt-2 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
            </div>

            {{-- 3 · Name (optional) --}}
            <div>
                <label for="label" class="field-label">3 · Name der Seite (optional)</label>
                <input id="label" type="text" wire:model="label" maxlength="60" placeholder="z. B. Katze" class="field">
                @error('label') <p class="mt-2 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-2.5 border-t-2 border-ink px-5 py-4">
            <a href="{{ route('admin.book.show', $book) }}" class="btn btn-plain flex-1 justify-start py-4 text-[15px]">Abbrechen</a>
            <button type="submit"
                    x-bind:disabled="!(uploaded && $wire.photo) || recState === 1 || uploading"
                    wire:loading.attr="disabled" wire:target="save"
                    class="btn btn-go flex-[2] justify-start py-4 text-[15px]">
                <span wire:loading.remove wire:target="save">Seite ins Buch zaubern</span>
                <span wire:loading wire:target="save">Datei fliegt zum Server …</span>
            </button>
        </div>
    </form>
</div>
