<?php

namespace App\Livewire\Admin;

use App\Models\MediaMessage;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Kopfzeile + Reiter des Admin-Bereichs. Fragt regelmäßig nach neuen Videos des Kindes.
 */
class Nav extends Component
{
    #[Locked]
    public string $active = 'books';

    public function mount(): void
    {
        $this->active = match (true) {
            request()->routeIs('admin.messages*') => 'messages',
            request()->routeIs('admin.settings') => 'settings',
            default => 'books',
        };
    }

    public function render(): View
    {
        return view('livewire.admin.nav', [
            'name' => Auth::user()?->name,
            'unread' => MediaMessage::unreadFromChildCount(),
            'pollSeconds' => config('handinhand.poll_seconds'),
        ]);
    }
}
