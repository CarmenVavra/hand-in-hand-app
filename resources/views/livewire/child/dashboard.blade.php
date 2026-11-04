<div class="flex h-full flex-col bg-sun" wire:poll.{{ $pollSeconds }}s>
    <header class="flex items-center gap-4 border-b-2 border-ink px-5 pt-5 pb-4">
        <div class="flex size-19 flex-none items-center justify-center overflow-hidden bg-ink text-4xl font-black text-sun">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="" class="size-full object-cover">
            @else
                <span aria-hidden="true">{{ $initial }}</span>
            @endif
        </div>
        <h1 class="text-[30px] leading-none font-black">
            @foreach ($nameLines as $line)
                {{ $line }}@if (! $loop->last)<br>@endif
            @endforeach
        </h1>
    </header>

    <a href="{{ route('child.record') }}"
       class="flex flex-1 items-center gap-5 border-b-2 border-ink bg-tomato px-5 text-left text-white active:bg-tomato-dark">
        <x-icon name="video" class="size-21 flex-none" />
        <span class="text-[32px] leading-[1.05] font-black">Nachricht<br>für dich</span>
    </a>

    <a href="{{ route('child.mailbox') }}"
       class="flex flex-1 items-center gap-5 border-b-2 border-ink bg-cobalt px-5 text-left text-white active:bg-cobalt-dark">
        <div class="relative flex-none {{ $hasNew ? 'animate-hop' : '' }}">
            <x-icon name="mail" class="size-21" />
            @if ($hasNew)
                <span class="absolute -top-2 -right-3.5 flex size-8.5 animate-blink items-center justify-center border-2 border-ink bg-sun text-xl font-black text-ink"
                      role="img" aria-label="Neue Nachricht">!</span>
            @endif
        </div>
        <span class="sr-only">Postkasten{{ $hasNew ? ' – neue Nachricht' : '' }}</span>
        <span class="text-[32px] leading-[1.05] font-black" aria-hidden="true">Post-<br>kasten</span>
    </a>

    <a href="{{ $bookUrl }}"
       class="flex flex-1 items-center gap-5 bg-leaf px-5 pb-[max(env(safe-area-inset-bottom),0.5rem)] text-left text-white active:bg-leaf-dark">
        <x-icon name="book" class="size-21 flex-none" />
        <span class="text-[32px] leading-[1.05] font-black">Unser<br>Bilderbuch</span>
    </a>
</div>
