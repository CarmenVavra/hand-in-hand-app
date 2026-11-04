<?php

namespace Tests\Feature;

use App\Livewire\Child\Record;
use App\Models\MediaMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\MakesFakeMedia;
use Tests\TestCase;

class ChildRecordTest extends TestCase
{
    use MakesFakeMedia, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_record_screen_renders_with_home_button(): void
    {
        $this->get(route('child.record'))
            ->assertOk()
            ->assertSee('Zum Hauptmenü')
            ->assertSee('Aufnahme starten');
    }

    public function test_recorded_video_is_saved_as_unread_message_from_the_child(): void
    {
        Livewire::test(Record::class)
            ->set('video', $this->fakeVideo())
            ->call('save')
            ->assertReturned(true)
            ->assertHasNoErrors();

        $message = MediaMessage::query()->sole();

        $this->assertNull($message->sender_id);
        $this->assertSame('video', $message->type);
        $this->assertFalse($message->is_read);
        Storage::disk('local')->assertExists($message->file_path);
    }

    public function test_non_video_upload_is_rejected(): void
    {
        Livewire::test(Record::class)
            ->set('video', UploadedFile::fake()->create('x.txt', 1, 'text/plain'))
            ->call('save')
            ->assertHasErrors('video');

        $this->assertDatabaseCount('media_messages', 0);
    }

    public function test_too_large_video_is_rejected(): void
    {
        config(['handinhand.limits.video_mb' => 1]);

        Livewire::test(Record::class)
            ->set('video', $this->fakeVideo('gross.mp4', 2048))
            ->call('save')
            ->assertHasErrors('video');

        $this->assertDatabaseCount('media_messages', 0);
    }

    public function test_save_without_a_file_is_rejected(): void
    {
        Livewire::test(Record::class)->call('save')->assertHasErrors('video');
    }
}
