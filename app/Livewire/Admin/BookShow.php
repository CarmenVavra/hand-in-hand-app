<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithToast;
use App\Models\PictureBook;
use App\Models\PictureBookPage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Bilderbuch')]
class BookShow extends Component
{
    use WithToast;

    #[Locked]
    public int $bookId;

    public string $title = '';

    public function mount(PictureBook $book): void
    {
        $this->bookId = $book->id;
        $this->title = $book->title;
    }

    public function moveUp(int $pageId): void
    {
        $this->page($pageId)->move(-1);
        $this->toast = null;
    }

    public function moveDown(int $pageId): void
    {
        $this->page($pageId)->move(1);
        $this->toast = null;
    }

    public function deletePage(int $pageId): void
    {
        $this->page($pageId)->deleteAndReindex();
        $this->toast('Seite wurde gelöscht.');
    }

    public function rename(): void
    {
        $data = $this->validate(['title' => ['required', 'string', 'min:2', 'max:80']]);

        $this->book()->update(['title' => $data['title']]);
        $this->toast('Der Titel wurde gespeichert.');
    }

    public function deleteBook(): void
    {
        $this->book()->delete();

        session()->flash('toast', 'Das Buch wurde gelöscht.');

        $this->redirectRoute('admin.home');
    }

    public function render(): View
    {
        $book = $this->book()->load('pages');
        $colors = config('handinhand.page_colors');

        return view('livewire.admin.book-show', compact('book', 'colors'));
    }

    private function book(): PictureBook
    {
        return PictureBook::query()->findOrFail($this->bookId);
    }

    /** Seiten dürfen nur aus dem aktuellen Buch bearbeitet werden. */
    private function page(int $pageId): PictureBookPage
    {
        return PictureBookPage::query()->where('picture_book_id', $this->bookId)->findOrFail($pageId);
    }
}
