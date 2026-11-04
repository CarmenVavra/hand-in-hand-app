<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithToast;
use App\Models\MediaMessage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Postkasten')]
class Messages extends Component
{
    use WithToast;

    /** Tante hat das Video des Kindes angesehen. */
    #[Renderless]
    public function markRead(int $id): void
    {
        MediaMessage::query()->fromChild()->whereKey($id)->first()?->markRead();
    }

    public function delete(int $id): void
    {
        MediaMessage::query()->whereKey($id)->first()?->delete();

        $this->toast('Nachricht wurde gelöscht.');
    }

    public function render(): View
    {
        return view('livewire.admin.messages', [
            'received' => MediaMessage::query()->fromChild()->latest()->limit(50)->get(),
            'sent' => MediaMessage::query()->fromAunt()->latest()->limit(50)->get(),
            'pollSeconds' => config('handinhand.poll_seconds'),
        ]);
    }
}
