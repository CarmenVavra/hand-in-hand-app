<?php

namespace App\Livewire\Child;

use App\Models\MediaMessage;
use App\Models\User;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::child')]
#[Title('Nachricht für dich')]
class Record extends Component
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $video;

    /** Wird vom Aufnahme-Skript nach dem Upload aufgerufen. Liefert true, wenn die Nachricht gespeichert ist. */
    public function save(): bool
    {
        $this->validate(['video' => Media::videoRules()]);

        $path = Media::disk()->putFile('messages/from-child', $this->video);

        MediaMessage::query()->create([
            'sender_id' => null, // Kind hat kein Konto
            'type' => MediaMessage::TYPE_VIDEO,
            'file_path' => $path,
            'is_read' => false,
        ]);

        $this->reset('video');

        return true;
    }

    public function render(): View
    {
        return view('livewire.child.record', [
            'auntName' => User::aunt()?->name ?: config('handinhand.aunt_name'),
            'maxSeconds' => config('handinhand.max_record_seconds'),
        ]);
    }
}
