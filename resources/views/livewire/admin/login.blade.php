<div>
    <p class="eyebrow">Admin</p>
    <h1 class="mt-1 text-[26px] leading-tight font-black">Anmelden</h1>

    <form wire:submit="login" class="mt-6 space-y-5">
        <div>
            <label for="email" class="field-label">E-Mail</label>
            <input id="email" type="email" wire:model="email" autocomplete="username" autofocus required class="field">
            @error('email') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Passwort</label>
            <input id="password" type="password" wire:model="password" autocomplete="current-password" required class="field">
            @error('password') <p class="mt-1 text-sm font-bold text-tomato" role="alert">{{ $message }}</p> @enderror
        </div>

        <label class="flex min-h-11 items-center gap-3 text-sm font-semibold">
            <input type="checkbox" wire:model="remember" class="size-5 accent-tomato">
            Angemeldet bleiben
        </label>

        <button type="submit" class="btn btn-primary w-full" wire:loading.attr="disabled">
            <span wire:loading.remove>Anmelden</span>
            <span wire:loading>Moment …</span>
        </button>
    </form>

    <p class="mt-6 text-xs text-mute">
        Passwort vergessen? Auf dem Server <code class="font-mono">php artisan handinhand:admin</code> ausführen.
    </p>
</div>
