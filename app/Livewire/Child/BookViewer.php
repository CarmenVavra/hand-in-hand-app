<?php

namespace App\Livewire\Child;

use App\Models\PictureBook;
use App\Models\PictureBookPage;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::child')]
#[Title('Unser Bilderbuch')]
class BookViewer extends Component
{
    #[Locked]
    public int $bookId;

    public function mount(PictureBook $book): void
    {
        $this->bookId = $book->id;
    }

    public function render(): View
    {
        $colors = config('handinhand.page_colors');

        $pages = PictureBookPage::query()
            ->where('picture_book_id', $this->bookId)
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->values()
            ->map(fn (PictureBookPage $page, int $index) => [
                'id' => $page->id,
                'image' => $page->imageUrl(),
                'audio' => $page->audioUrl(),
                'label' => $page->label,
                'color' => $colors[$index % count($colors)],
            ])
            ->all();

        return view('livewire.child.book-viewer', compact('pages'));
    }
}
