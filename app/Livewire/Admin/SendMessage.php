<?php

namespace App\Livewire\Admin;

use App\Models\MediaMessage;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
#[Title('Neue Nachricht')]
class SendMessage extends Component
{
    use WithFileUploads;

    /** @var TemporaryUploadedFile|null */
    public $file;

    public string $title = '';

    public function save(): void
    {
        $this->validate([
            'file' => Media::videoOrAudioRules(),
            'title' => ['nullable', 'string', 'max:40'],
        ]);

        $mime = (string) $this->file->getMimeType();
        $isAudio = str_starts_with($mime, 'audio/');

        MediaMessage::query()->create([
            'sender_id' => Auth::id(),
            'type' => $isAudio ? MediaMessage::TYPE_AUDIO : MediaMessage::TYPE_VIDEO,
            'title' => $this->title !== '' ? $this->title : null,
            'file_path' => Media::disk()->putFile('messages/from-aunt', $this->file),
            'is_read' => false,
        ]);

        session()->flash('toast', 'Die Nachricht liegt im Postkasten der Kleinen!');

        $this->redirectRoute('admin.messages');
    }

    public function render(): View
    {
        return view('livewire.admin.send-message', [
            'maxMb' => config('handinhand.limits.video_mb'),
        ]);
    }
}
