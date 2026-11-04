<?php

namespace App\Livewire\Admin;

use App\Models\PictureBook;
use App\Services\ImageOptimizer;
use App\Support\Media;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts::admin')]
#[Title('Neue Bilderbuch-Seite')]
class CreatePage extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $bookId;

    /** @var TemporaryUploadedFile|null */
    public $photo;

    /** @var TemporaryUploadedFile|null */
    public $audio;

    public string $label = '';

    public function mount(PictureBook $book): void
    {
        $this->bookId = $book->id;
    }

    public function save(ImageOptimizer $images): void
    {
        $this->validate([
            'photo' => Media::imageRules(),
            'audio' => Media::audioRules(),
            'label' => ['nullable', 'string', 'max:60'],
        ]);

        $book = PictureBook::query()->findOrFail($this->bookId);

        $imagePath = $images->store($this->photo, 'books/images');
        $audioPath = Media::disk()->putFile('books/audios', $this->audio);

        $book->addPage($imagePath, $audioPath, $this->label !== '' ? $this->label : null);

        session()->flash('toast', 'Die Seite wurde erfolgreich hinzugefügt!');

        $this->redirectRoute('admin.book.show', $book);
    }

    public function render(): View
    {
        $book = PictureBook::query()->findOrFail($this->bookId);

        return view('livewire.admin.create-page', [
            'book' => $book,
            'photoPreview' => $this->photo && $this->photo->isPreviewable() ? $this->photo->temporaryUrl() : null,
        ]);
    }
}
