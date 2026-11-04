<?php

namespace Tests\Feature;

use App\Livewire\Admin\Messages;
use App\Livewire\Admin\SendMessage;
use App\Models\MediaMessage;
use App\Models\User;
use App\Support\Media;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\MakesFakeMedia;
use Tests\TestCase;

class AdminMessagesTest extends TestCase
{
    use MakesFakeMedia, RefreshDatabase;

    private User $aunt;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        $this->aunt = User::factory()->create();
        $this->actingAs($this->aunt);
    }

    public function test_aunt_sends_a_video_to_the_child(): void
    {
        Livewire::test(SendMessage::class)
            ->set('file', $this->fakeVideo())
            ->set('title', 'Der Bagger')
            ->call('save')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.messages'));

        $message = MediaMessage::query()->sole();
        $this->assertSame($this->aunt->id, $message->sender_id);
        $this->assertSame('video', $message->type);
        $this->assertSame('Der Bagger', $message->title);
        $this->assertFalse($message->is_read);
        Storage::disk('local')->assertExists($message->file_path);
    }

    public function test_aunt_can_send_a_voice_message(): void
    {
        Livewire::test(SendMessage::class)->set('file', $this->fakeAudio())->call('save')->assertHasNoErrors();

        $this->assertSame('audio', MediaMessage::query()->sole()->type);
    }

    public function test_unsupported_files_are_rejected(): void
    {
        Livewire::test(SendMessage::class)
            ->set('file', UploadedFile::fake()->create('x.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasErrors('file');

        $this->assertDatabaseCount('media_messages', 0);
    }

    public function test_inbox_shows_child_videos_and_marks_them_read(): void
    {
        $video = MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'v.webm']);

        $component = Livewire::test(Messages::class)->assertSee('Von der Kleinen (1)')->assertSee('NEU');
        $this->assertSame(1, MediaMessage::unreadFromChildCount());

        $component->call('markRead', $video->id);

        $this->assertSame(0, MediaMessage::unreadFromChildCount());
    }

    public function test_aunt_cannot_mark_her_own_messages_as_read_here(): void
    {
        $own = MediaMessage::query()->create(['sender_id' => $this->aunt->id, 'type' => 'video', 'file_path' => 'v.mp4']);

        Livewire::test(Messages::class)->call('markRead', $own->id);

        $this->assertFalse($own->fresh()->is_read);
    }

    public function test_deleting_a_message_removes_its_file(): void
    {
        Media::disk()->put('messages/from-child/v.webm', 'x');
        $video = MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'messages/from-child/v.webm']);

        Livewire::test(Messages::class)->call('delete', $video->id)->assertSee('Nachricht wurde gelöscht.');

        $this->assertDatabaseCount('media_messages', 0);
        Storage::disk('local')->assertMissing('messages/from-child/v.webm');
    }

    public function test_navigation_shows_unread_count(): void
    {
        MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'a.webm']);
        MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => 'b.webm']);

        $this->get(route('admin.home'))->assertOk()->assertSee('2 neu');
    }
}
