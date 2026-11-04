<?php

namespace App\Livewire\Child;

use App\Models\MediaMessage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::child')]
#[Title('Postkasten')]
class Mailbox extends Component
{
    /** Das Kind hat die Nachricht geöffnet -> "gelesen" (der Postkasten hört auf zu hüpfen). */
    #[Renderless]
    public function markRead(int $id): void
    {
        MediaMessage::query()->fromAunt()->whereKey($id)->first()?->markRead();
    }

    public function render(): View
    {
        $colors = config('handinhand.tile_colors');

        $items = MediaMessage::query()
            ->fromAunt()
            ->whereIn('type', [MediaMessage::TYPE_VIDEO, MediaMessage::TYPE_AUDIO])
            ->latest()
            ->limit(40)
            ->get()
            ->map(fn (MediaMessage $message) => [
                'id' => $message->id,
                'title' => $message->displayTitle(),
                'url' => $message->url(),
                'color' => $colors[$message->id % count($colors)],
                'isAudio' => $message->isAudio(),
                'isNew' => ! $message->is_read,
            ])
            ->values()
            ->all();

        // "Tante Carmen" -> "Video für Tante", sonst "Video für <Name>"
        $name = User::aunt()?->name ?: config('handinhand.aunt_name');
        $sendLabel = 'Video für '.(Str::startsWith($name, ['Tante ', 'Onkel ']) ? Str::before($name, ' ') : $name);

        return view('livewire.child.mailbox', compact('items', 'sendLabel'));
    }
}
