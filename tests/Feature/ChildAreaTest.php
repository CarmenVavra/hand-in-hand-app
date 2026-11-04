<?php

namespace Tests\Feature;

use App\Livewire\Child\BookSelect;
use App\Livewire\Child\BookViewer;
use App\Livewire\Child\Dashboard;
use App\Livewire\Child\Mailbox;
use App\Models\MediaMessage;
use App\Models\PictureBook;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChildAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_home_menu(): void
    {
        $this->get('/')->assertRedirect('/home');
    }

    public function test_dashboard_shows_three_tiles_and_aunt_name(): void
    {
        User::factory()->create(['name' => 'Tante Carmen']);

        $this->get(route('child.dashboard'))
            ->assertOk()
            ->assertSee('Nachricht')
            ->assertSee('kasten')
            ->assertSee('Bilderbuch')
            ->assertSee('Tante')
            ->assertSee('Carmen')
            ->assertSee(route('child.record'), false)
            ->assertSee(route('child.mailbox'), false);
    }

    public function test_mailbox_tile_hops_only_while_unread_videos_exist(): void
    {
        $aunt = User::factory()->create();

        Livewire::test(Dashboard::class)->assertDontSee('animate-hop')->assertDontSee('Neue Nachricht');

        $message = MediaMessage::query()->create(['sender_id' => $aunt->id, 'type' => 'video', 'file_path' => 'x.mp4']);

        Livewire::test(Dashboard::class)->assertSee('animate-hop')->assertSee('Neue Nachricht');

        $message->markRead();

        Livewire::test(Dashboard::class)->assertDontSee('animate-hop');
    }

    public function test_videos_from_the_child_do_not_make_the_mailbox_hop(): void
    {
        MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'x.mp4']);

        Livewire::test(Dashboard::class)->assertDontSee('animate-hop');
    }

    public function test_book_tile_links_directly_to_the_only_book(): void
    {
        $book = PictureBook::query()->create(['title' => 'Tiere']);
        $book->addPage('a.jpg', 'a.wav');

        Livewire::test(Dashboard::class)->assertSee(route('child.book.view', $book), false);
    }

    public function test_book_tile_links_to_selection_with_several_books(): void
    {
        foreach (['A', 'B'] as $title) {
            PictureBook::query()->create(['title' => $title])->addPage('a.jpg', 'a.wav');
        }

        Livewire::test(Dashboard::class)->assertSee(route('child.book.select'), false);
        Livewire::test(BookSelect::class)->assertSee('A')->assertSee('B');
    }

    public function test_mailbox_lists_only_messages_from_the_aunt_newest_first(): void
    {
        $aunt = User::factory()->create();
        MediaMessage::query()->create(['sender_id' => $aunt->id, 'type' => 'video', 'title' => 'Der Bagger', 'file_path' => 'a.mp4']);
        MediaMessage::query()->create(['sender_id' => $aunt->id, 'type' => 'video', 'title' => 'Hund im Park', 'file_path' => 'b.mp4']);
        MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'title' => 'Geheim vom Kind', 'file_path' => 'c.mp4']);

        Livewire::test(Mailbox::class)
            ->assertSeeInOrder(['Hund im Park', 'Der Bagger'])
            ->assertDontSee('Geheim vom Kind');
    }

    public function test_opening_a_video_marks_it_as_read(): void
    {
        $aunt = User::factory()->create();
        $message = MediaMessage::query()->create(['sender_id' => $aunt->id, 'type' => 'video', 'file_path' => 'a.mp4']);

        Livewire::test(Mailbox::class)->call('markRead', $message->id);

        $this->assertTrue($message->fresh()->is_read);
    }

    public function test_child_cannot_mark_its_own_videos_via_the_mailbox(): void
    {
        $own = MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'a.mp4']);

        Livewire::test(Mailbox::class)->call('markRead', $own->id);

        $this->assertFalse($own->fresh()->is_read);
    }

    public function test_book_viewer_gets_pages_in_order(): void
    {
        $book = PictureBook::query()->create(['title' => 'Tiere']);
        $first = $book->addPage('1.jpg', '1.wav', 'Katze');
        $second = $book->addPage('2.jpg', '2.wav', 'Hund');

        $this->get(route('child.book.view', $book))->assertOk();

        Livewire::test(BookViewer::class, ['book' => $book])
            ->assertViewHas('pages', fn (array $pages) => array_column($pages, 'image') === [
                route('media.page.image', $first),
                route('media.page.image', $second),
            ] && array_column($pages, 'audio') === [
                route('media.page.audio', $first),
                route('media.page.audio', $second),
            ]);
    }

    public function test_empty_book_shows_friendly_hint_and_unknown_book_is_404(): void
    {
        $book = PictureBook::query()->create(['title' => 'Leer']);

        Livewire::test(BookViewer::class, ['book' => $book])->assertSee('Hier ist noch kein Bild drin!');

        $this->get(route('child.book.view', 999))->assertNotFound();
    }

    public function test_legal_pages_and_offline_page_are_reachable(): void
    {
        $this->get(route('legal.impressum'))->assertOk()->assertSee('Impressum');
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Datenschutzerklärung');
        $this->get(route('pwa.offline'))->assertOk();
    }

    public function test_manifest_and_service_worker_are_served_statically(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('HandInHand', $manifest['name']); // Tippfehler "none" aus dem Konzept ist behoben
        $this->assertSame('/home', $manifest['start_url']);
        $this->assertFileExists(public_path('service-worker.js'));

        foreach ($manifest['icons'] as $icon) {
            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
            $this->assertStringNotContainsString('/icons/', $icon['src'].'/'); // netcup/Apache-Alias /icons/
        }
    }
}
