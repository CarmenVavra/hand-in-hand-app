<?php

namespace Tests\Feature;

use App\Livewire\Admin\BookShow;
use App\Livewire\Admin\CreatePage;
use App\Livewire\Admin\Home;
use App\Models\PictureBook;
use App\Models\PictureBookPage;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\MakesFakeMedia;
use Tests\TestCase;

class AdminBookTest extends TestCase
{
    use MakesFakeMedia, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->actingAs(User::factory()->create());
    }

    /** @return array{PictureBook, list<PictureBookPage>} */
    private function bookWithPages(int $count = 3): array
    {
        $book = PictureBook::query()->create(['title' => 'Tiere im Garten']);
        $pages = [];

        foreach (range(1, $count) as $n) {
            Media::disk()->put($image = "books/images/{$n}.jpg", 'img');
            Media::disk()->put($audio = "books/audios/{$n}.webm", 'aud');
            $pages[] = $book->addPage($image, $audio, "Seite{$n}");
        }

        return [$book, $pages];
    }

    public function test_overview_lists_books_with_page_counts(): void
    {
        $this->bookWithPages(2);

        Livewire::test(Home::class)->assertSee('Tiere im Garten')->assertSee('2 Seiten');
    }

    public function test_aunt_can_create_a_book_and_lands_on_new_page_form(): void
    {
        Livewire::test(Home::class)
            ->set('title', 'Mein Garten')
            ->call('createBook')
            ->assertRedirect(route('admin.page.create', PictureBook::query()->where('title', 'Mein Garten')->sole()));
    }

    public function test_book_title_is_required(): void
    {
        Livewire::test(Home::class)->set('title', '')->call('createBook')->assertHasErrors('title');
    }

    public function test_page_needs_photo_and_voice(): void
    {
        [$book] = $this->bookWithPages(0);

        Livewire::test(CreatePage::class, ['book' => $book])->call('save')->assertHasErrors(['photo', 'audio']);

        Livewire::test(CreatePage::class, ['book' => $book])
            ->set('photo', $this->fakeImage())
            ->call('save')
            ->assertHasErrors('audio');
    }

    public function test_aunt_adds_a_page_with_photo_and_voice(): void
    {
        [$book] = $this->bookWithPages(2);

        Livewire::test(CreatePage::class, ['book' => $book])
            ->set('photo', $this->fakeImage('katze.jpg', 3000, 2000))
            ->set('audio', $this->fakeAudio())
            ->set('label', 'Katze')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.book.show', $book));

        $page = $book->pages()->get()->last();
        $this->assertSame(3, $page->order_column);
        $this->assertSame('Katze', $page->label);
        Storage::disk('local')->assertExists([$page->image_path, $page->audio_path]);

        // Foto wurde verkleinert (längste Kante max. 1600 px)
        [$width, $height] = getimagesizefromstring(Storage::disk('local')->get($page->image_path));
        $this->assertSame(1600, max($width, $height));
    }

    public function test_page_numbers_continue_after_gaps(): void
    {
        [$book, $pages] = $this->bookWithPages(3);
        $pages[1]->delete(); // Lücke in der Nummerierung (1, 3)

        $new = $book->addPage('x.jpg', 'x.webm');

        $this->assertSame(4, $new->order_column);
    }

    public function test_pages_can_be_moved_up_and_down(): void
    {
        [$book, $pages] = $this->bookWithPages(3);
        $order = fn () => $book->pages()->pluck('label')->all();

        Livewire::test(BookShow::class, ['book' => $book])->call('moveDown', $pages[0]->id);
        $this->assertSame(['Seite2', 'Seite1', 'Seite3'], $order());

        Livewire::test(BookShow::class, ['book' => $book])->call('moveUp', $pages[2]->id);
        $this->assertSame(['Seite2', 'Seite3', 'Seite1'], $order());
    }

    public function test_moving_the_first_page_up_or_last_down_changes_nothing(): void
    {
        [$book, $pages] = $this->bookWithPages(2);

        Livewire::test(BookShow::class, ['book' => $book])->call('moveUp', $pages[0]->id)->call('moveDown', $pages[1]->id);

        $this->assertSame(['Seite1', 'Seite2'], $book->pages()->pluck('label')->all());
    }

    public function test_deleting_a_page_removes_files_and_renumbers(): void
    {
        [$book, $pages] = $this->bookWithPages(3);

        Livewire::test(BookShow::class, ['book' => $book])
            ->call('deletePage', $pages[1]->id)
            ->assertSee('Seite wurde gelöscht.');

        Storage::disk('local')->assertMissing([$pages[1]->image_path, $pages[1]->audio_path]);
        Storage::disk('local')->assertExists([$pages[0]->image_path, $pages[2]->audio_path]);
        $this->assertSame([1, 2], $book->pages()->pluck('order_column')->all());
        $this->assertSame(['Seite1', 'Seite3'], $book->pages()->pluck('label')->all());
    }

    public function test_pages_of_other_books_cannot_be_touched(): void
    {
        [$book] = $this->bookWithPages(1);
        [, $foreign] = (function () {
            $other = PictureBook::query()->create(['title' => 'Anderes']);

            return [$other, [$other->addPage('f.jpg', 'f.webm')]];
        })();

        Livewire::test(BookShow::class, ['book' => $book])->call('deletePage', $foreign[0]->id)->assertStatus(404);

        $this->assertDatabaseHas('picture_book_pages', ['id' => $foreign[0]->id]);
    }

    public function test_deleting_a_book_removes_all_its_files(): void
    {
        [$book, $pages] = $this->bookWithPages(2);

        Livewire::test(BookShow::class, ['book' => $book])->call('deleteBook')->assertRedirect(route('admin.home'));

        $this->assertDatabaseCount('picture_books', 0);
        $this->assertDatabaseCount('picture_book_pages', 0);
        Storage::disk('local')->assertMissing(collect($pages)->flatMap(fn ($p) => [$p->image_path, $p->audio_path])->all());
    }

    public function test_book_can_be_renamed(): void
    {
        [$book] = $this->bookWithPages(0);

        Livewire::test(BookShow::class, ['book' => $book])->set('title', 'Neuer Titel')->call('rename')->assertSee('Der Titel wurde gespeichert.');

        $this->assertSame('Neuer Titel', $book->fresh()->title);
    }
}
