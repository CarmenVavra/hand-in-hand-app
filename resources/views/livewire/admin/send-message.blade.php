<div>
    <div class="border-b-2 border-ink px-5 py-5">
        <p class="eyebrow">Postkasten</p>
        <h1 class="mt-1 text-[26px] leading-tight font-black">Neue Nachricht für die Kleine</h1>
    </div>

    <form wire:submit="save" class="flex flex-col">
        <div class="space-y-5 p-5">
            <div x-data="{ uploading: false, progress: 0 }"
                 x-on:livewire-upload-start="uploading = true; progress = 0"
                 x-on:livewire-upload-finish="uploading = false"
                 x-on:livewire-upload-error="uploading = false"
                 x-on:livewire-upload-progress="progress = $event.detail.progress">
                <span class="field-label" id="file-label">1 · Video oder Sprachnachricht</span>

                <label for="file"
                       class="flex min-h-32 w-full cursor-pointer items-end border-2 border-ink p-3.5 text-left text-[15px] font-extrabold focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-tomato
                              {{ $file ? 'bg-sun' : 'bg-white' }}">
                    <span class="flex items-center gap-2">
                        @if ($file)
                            <x-icon name="check" class="size-5" /> {{ $file->getClientOriginalName() }}
                        @else
                            <x-icon name="video" class="size-6" /> + Aufnehmen oder aus der Galerie wählen
                        @endif
                    </span>
                </label>
                <input id="file" type="file" wire:model="file" accept="video/*,audio/*" class="sr-only" aria-labelledby="file-label">

                <div x-show="uploading" x-cloak class="mt-2 h-2 bg-line" role="progressbar" aria-label="Wird hochgeladen">
                    <div class="h-full bg-ink" x-bind:style="'width:' + progress + '%'"></div>
                </div>
                <p class="mt-2 text-xs text-mute">Höchstens {{ $maxMb }} MB. Kurze Clips (10–20 Sekunden) passen am besten.</p>
                @error('file') <p class="mt-2 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="title" class="field-label">2 · Titel (steht auf der Kachel)</label>
                <input id="title" type="text" wire:model="title" maxlength="40" placeholder="z. B. Der Bagger" class="field">
                @error('title') <p class="mt-2 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex gap-2.5 border-t-2 border-ink px-5 py-4">
            <a href="{{ route('admin.messages') }}" class="btn btn-plain flex-1 justify-start py-4 text-[15px]">Abbrechen</a>
            <button type="submit" @disabled(! $file) wire:loading.attr="disabled" wire:target="save,file"
                    class="btn btn-go flex-[2] justify-start py-4 text-[15px]">
                <span wire:loading.remove wire:target="save">In den Postkasten legen</span>
                <span wire:loading wire:target="save">Wird gesendet …</span>
            </button>
        </div>
    </form>
</div>
