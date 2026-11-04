<div wire:poll.{{ $pollSeconds }}s>
    <div class="flex items-end justify-between gap-3 border-b-2 border-ink px-5 py-5">
        <div>
            <h1 class="text-[26px] leading-tight font-black">Postkasten</h1>
            <p class="mt-0.5 text-sm">Videos von und für die Kleine</p>
        </div>
        <a href="{{ route('admin.messages.create') }}" class="btn btn-primary flex-none">+ Neu</a>
    </div>

    <x-toast :message="$toast" />

    {{-- Von der Kleinen --}}
    <section aria-labelledby="received-title">
        <h2 id="received-title" class="border-b-2 border-ink bg-cobalt px-5 py-2 text-xs font-extrabold tracking-wider text-white uppercase">
            Von der Kleinen ({{ $received->count() }})
        </h2>

        <ul class="bg-white">
            @forelse ($received as $message)
                <li wire:key="in-{{ $message->id }}" class="border-b-2 border-ink px-4 py-3">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[15px] font-extrabold">
                            Video vom {{ $message->created_at->format('d.m.Y, H:i') }} Uhr
                            @unless ($message->is_read)
                                <span class="ml-1 bg-sun px-1.5 py-0.5 align-middle text-xs font-black">NEU</span>
                            @endunless
                        </p>
                        <button type="button" x-data aria-label="Video löschen"
                                x-on:click="$dispatch('confirm-dialog', {
                                    title: 'Video löschen?',
                                    message: 'Das Video wird endgültig gelöscht.',
                                    confirmLabel: 'Löschen',
                                    onConfirm: () => $wire.delete({{ $message->id }}),
                                })"
                                class="h-8 w-9 flex-none border-2 border-ink bg-tomato font-black text-white hover:bg-tomato-dark">✕</button>
                    </div>
                    <video src="{{ $message->url() }}" controls playsinline preload="metadata"
                           x-data x-on:play.once="$wire.markRead({{ $message->id }})"
                           class="mt-2 max-h-80 w-full bg-ink"></video>
                </li>
            @empty
                <li class="border-b-2 border-ink px-5 py-5 text-sm">Noch kein Video von der Kleinen.</li>
            @endforelse
        </ul>
    </section>

    {{-- An die Kleine --}}
    <section aria-labelledby="sent-title">
        <h2 id="sent-title" class="border-b-2 border-ink bg-tomato px-5 py-2 text-xs font-extrabold tracking-wider text-white uppercase">
            An die Kleine geschickt ({{ $sent->count() }})
        </h2>

        <ul class="bg-white">
            @forelse ($sent as $message)
                <li wire:key="out-{{ $message->id }}" class="flex items-center gap-3 border-b-2 border-ink px-4 py-3">
                    <x-icon name="{{ $message->isAudio() ? 'speaker' : 'play' }}" class="size-6 flex-none" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[15px] font-extrabold">{{ $message->displayTitle() }}</p>
                        <p class="text-xs">
                            {{ $message->created_at->format('d.m.Y, H:i') }} Uhr ·
                            {{ $message->is_read ? 'gesehen ✓' : 'noch nicht gesehen' }}
                        </p>
                    </div>
                    <button type="button" x-data aria-label="Nachricht löschen"
                            x-on:click="$dispatch('confirm-dialog', {
                                title: 'Nachricht löschen?',
                                message: @js('„'.$message->displayTitle().'“ wird endgültig gelöscht.'),
                                confirmLabel: 'Löschen',
                                onConfirm: () => $wire.delete({{ $message->id }}),
                            })"
                            class="h-8 w-9 flex-none border-2 border-ink bg-tomato font-black text-white hover:bg-tomato-dark">✕</button>
                </li>
            @empty
                <li class="border-b-2 border-ink px-5 py-5 text-sm">Du hast noch nichts geschickt. Tippe auf „+ Neu“.</li>
            @endforelse
        </ul>
    </section>
</div>
