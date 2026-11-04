<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\WithToast;
use App\Models\PictureBook;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts::admin')]
#[Title('Bilderbücher')]
class Home extends Component
{
    use WithToast;

    public string $title = '';

    public function createBook(): void
    {
        $data = $this->validate(['title' => ['required', 'string', 'min:2', 'max:80']]);

        $book = PictureBook::query()->create(['title' => $data['title']]);

        session()->flash('toast', 'Das Buch wurde angelegt. Jetzt die erste Seite hinzufügen!');

        $this->redirectRoute('admin.page.create', $book);
    }

    public function render(): View
    {
        $books = PictureBook::query()->withCount('pages')->with('pages')->orderBy('title')->get();

        return view('livewire.admin.home', compact('books'));
    }
}
