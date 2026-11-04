<header class="border-b-2 border-ink px-5 pt-5" wire:poll.{{ $pollSeconds }}s>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="eyebrow">Admin · {{ $name }}</p>
            <a href="{{ route('admin.home') }}" class="mt-1 block text-[26px] leading-tight font-black">
                Hand<span class="text-tomato">In</span>Hand
            </a>
        </div>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button type="submit" class="btn btn-plain">Abmelden</button>
        </form>
    </div>

    <nav class="mt-4 flex flex-wrap gap-1" aria-label="Admin-Navigation">
        @php
            $tabs = [
                'books' => ['Bücher', route('admin.home')],
                'messages' => ['Postkasten', route('admin.messages')],
                'settings' => ['Einstellungen', route('admin.settings')],
            ];
        @endphp
        @foreach ($tabs as $key => [$label, $url])
            <a href="{{ $url }}"
               @if ($active === $key) aria-current="page" @endif
               class="-mb-0.5 flex min-h-11 items-center gap-2 border-2 border-b-0 border-ink px-3 py-2 text-sm sm:px-4 font-extrabold whitespace-nowrap
                      {{ $active === $key ? 'bg-ink text-white' : 'bg-white hover:bg-sun' }}">
                {{ $label }}
                @if ($key === 'messages' && $unread > 0)
                    <span class="bg-tomato px-1.5 text-xs font-black text-white" aria-label="{{ $unread }} neu">{{ $unread }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</header>
