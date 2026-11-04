<?php

namespace Tests\Feature;

use App\Models\MediaMessage;
use App\Models\PictureBook;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    private function message(?int $senderId, string $path): MediaMessage
    {
        Media::disk()->put($path, 'video-bytes');

        return MediaMessage::query()->create(['sender_id' => $senderId, 'type' => 'video', 'file_path' => $path]);
    }

    public function test_child_can_load_videos_from_the_aunt(): void
    {
        $aunt = User::factory()->create();
        $message = $this->message($aunt->id, 'messages/from-aunt/a.mp4');

        $this->get($message->url())->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_videos_from_the_child_are_only_visible_to_the_logged_in_aunt(): void
    {
        $message = $this->message(null, 'messages/from-child/c.webm');

        $this->get($message->url())->assertForbidden();

        $this->actingAs(User::factory()->create())->get($message->url())->assertOk();
    }

    public function test_range_requests_are_supported_for_seeking(): void
    {
        $aunt = User::factory()->create();
        $message = $this->message($aunt->id, 'messages/from-aunt/a.mp4');

        $this->get($message->url(), ['Range' => 'bytes=0-4'])->assertStatus(206);
    }

    public function test_missing_files_give_404(): void
    {
        $aunt = User::factory()->create();
        $message = MediaMessage::query()->create(['sender_id' => $aunt->id, 'type' => 'video', 'file_path' => 'messages/weg.mp4']);

        $this->get($message->url())->assertNotFound();
    }

    public function test_book_page_media_and_profile_photo_are_served(): void
    {
        Media::disk()->put('books/images/a.jpg', 'img');
        Media::disk()->put('books/audios/a.webm', 'aud');
        $page = PictureBook::query()->create(['title' => 'T'])->addPage('books/images/a.jpg', 'books/audios/a.webm');

        $this->get($page->imageUrl())->assertOk();
        $this->get($page->audioUrl())->assertOk();

        $this->get(route('media.profile'))->assertNotFound();

        Media::disk()->put('profile/p.jpg', 'img');
        User::factory()->create(['photo_path' => 'profile/p.jpg']);
        $this->get(route('media.profile'))->assertOk();
    }

    public function test_files_are_not_publicly_reachable_via_storage_url(): void
    {
        $this->get('/storage/messages/from-child/c.webm')->assertNotFound();
    }
}
