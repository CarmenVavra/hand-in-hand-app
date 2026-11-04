<?php

namespace App\Livewire\Child;

use App\Models\MediaMessage;
use App\Models\PictureBook;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::child')]
#[Title('Hauptmenü')]
class Dashboard extends Component
{
    public function render(): View
    {
        $aunt = User::aunt();
        $name = $aunt?->name ?: config('handinhand.aunt_name');

        // Hat das Kind genau ein Buch, führt die Kachel direkt hinein – sonst zur Auswahl.
        $bookIds = PictureBook::query()->has('pages')->limit(2)->pluck('id');
        $bookUrl = $bookIds->count() === 1
            ? route('child.book.view', $bookIds->first())
            : route('child.book.select');

        return view('livewire.child.dashboard', [
            'hasNew' => MediaMessage::hasUnreadForChild(),
            'photoUrl' => $aunt?->photo_path ? route('media.profile', ['v' => md5($aunt->photo_path)]) : null,
            'nameLines' => Str::of($name)->explode(' ', 2)->all(),
            'initial' => Str::upper(Str::substr($name, 0, 1)),
            'bookUrl' => $bookUrl,
            'pollSeconds' => config('handinhand.poll_seconds'),
        ]);
    }
}
