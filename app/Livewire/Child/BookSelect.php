<?php

namespace App\Livewire\Child;

use App\Models\PictureBook;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::child')]
#[Title('Unser Bilderbuch')]
class BookSelect extends Component
{
    public function render(): View
    {
        $colors = config('handinhand.page_colors');

        $books = PictureBook::query()
            ->has('pages')
            ->with('pages')
            ->orderBy('title')
            ->get()
            ->map(fn (PictureBook $book) => [
                'id' => $book->id,
                'title' => $book->title,
                'cover' => $book->coverUrl(),
                'color' => $colors[$book->id % count($colors)],
            ]);

        return view('livewire.child.book-select', compact('books'));
    }
}
