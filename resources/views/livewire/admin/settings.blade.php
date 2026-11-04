<div>
    <div class="border-b-2 border-ink px-5 py-5">
        <h1 class="text-[26px] leading-tight font-black">Einstellungen</h1>
    </div>

    <x-toast :message="$toast" />

    {{-- Profil --}}
    <form wire:submit="saveProfile" class="space-y-4 border-b-2 border-ink bg-white p-5">
        <h2 class="text-lg font-black">Dein Profil</h2>

        <div>
            <label for="name" class="field-label">Anzeigename (steht oben im Hauptmenü)</label>
            <input id="name" type="text" wire:model="name" maxlength="60" required class="field">
            @error('name') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="photo" class="field-label">Dein Foto</label>
            <div class="flex items-center gap-4">
                <span class="flex size-19 flex-none items-center justify-center overflow-hidden bg-ink text-3xl font-black text-sun">
                    @if ($photo && $photo->isPreviewable())
                        <img src="{{ $photo->temporaryUrl() }}" alt="Vorschau" class="size-full object-cover">
                    @elseif ($user->photo_path)
                        <img src="{{ route('media.profile', ['v' => md5($user->photo_path)]) }}" alt="Aktuelles Foto" class="size-full object-cover">
                    @else
                        <span aria-hidden="true">{{ mb_substr($user->name, 0, 1) }}</span>
                    @endif
                </span>
                <input id="photo" type="file" wire:model="photo" accept="image/*" class="field text-sm">
            </div>
            @error('photo') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn btn-go" wire:loading.attr="disabled" wire:target="saveProfile,photo">Speichern</button>
    </form>

    {{-- Passwort --}}
    <form wire:submit="savePassword" class="space-y-4 border-b-2 border-ink bg-white p-5">
        <h2 class="text-lg font-black">Passwort ändern</h2>

        <div>
            <label for="current_password" class="field-label">Aktuelles Passwort</label>
            <input id="current_password" type="password" wire:model="current_password" autocomplete="current-password" required class="field">
            @error('current_password') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="field-label">Neues Passwort (mind. 10 Zeichen)</label>
            <input id="password" type="password" wire:model="password" autocomplete="new-password" required class="field">
            @error('password') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="field-label">Neues Passwort wiederholen</label>
            <input id="password_confirmation" type="password" wire:model="password_confirmation" autocomplete="new-password" required class="field">
        </div>

        <button type="submit" class="btn btn-go" wire:loading.attr="disabled" wire:target="savePassword">Passwort ändern</button>
    </form>

    {{-- Kinder-Handy --}}
    <section class="space-y-3 p-5" x-data="{ copied: false }">
        <h2 class="text-lg font-black">Handy der Kleinen</h2>

        @if ($childLink)
            <p class="text-sm">Öffne diesen Link <strong>einmalig</strong> auf dem Handy der Kleinen. Danach ist das Gerät freigeschaltet – ohne diesen Link kommt niemand in den Kinderbereich.</p>
            <input type="text" readonly value="{{ $childLink }}" class="field font-mono text-sm" aria-label="Freischalt-Link" x-ref="link" x-on:focus="$el.select()">
            <button type="button" class="btn btn-plain"
                    x-on:click="navigator.clipboard.writeText($refs.link.value); copied = true; setTimeout(() => copied = false, 2000)">
                <span x-show="!copied">Link kopieren</span><span x-show="copied" x-cloak>Kopiert ✓</span>
            </button>
        @else
            <p class="text-sm">Der Kinderbereich ist derzeit <strong>ohne Zugangsschutz</strong> erreichbar. Setze in der <code class="font-mono">.env</code> den Wert <code class="font-mono">HIH_CHILD_TOKEN</code> (zufälliger, langer Text), bevor die App live geht.</p>
        @endif
    </section>
</div>
