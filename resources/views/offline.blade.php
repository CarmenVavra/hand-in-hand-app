<x-page title="Offline">
    <div class="flex flex-col items-center gap-5 py-10 text-center">
        <x-icon name="mail" class="size-20" />
        <h1 class="text-2xl font-black">Gerade kein Netz</h1>
        <p class="text-sm">Sobald das Handy wieder Internet hat, geht es weiter.</p>
        <a href="{{ route('child.dashboard') }}" class="btn btn-primary">Nochmal versuchen</a>
    </div>
</x-page>
