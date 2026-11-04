<?php

namespace App\Http\Controllers;

use App\Models\MediaMessage;
use App\Models\PictureBookPage;
use App\Models\User;
use App\Support\Media;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Liefert Medien aus dem privaten Speicher aus (inkl. Range-Requests für Video-Vorspulen auf iOS).
 * Es gibt keinen öffentlichen /storage-Link: Dateien sind nur über diese Routen erreichbar.
 */
class MediaController extends Controller
{
    public function message(MediaMessage $message): BinaryFileResponse
    {
        // Videos vom Kind sieht nur Tante (eingeloggt) – Kinderbereich-Zugang genügt dafür nicht.
        abort_if($message->isFromChild() && ! Auth::check(), 403);

        return $this->file($message->file_path);
    }

    public function pageImage(PictureBookPage $page): BinaryFileResponse
    {
        return $this->file($page->image_path);
    }

    public function pageAudio(PictureBookPage $page): BinaryFileResponse
    {
        return $this->file($page->audio_path);
    }

    public function profile(): BinaryFileResponse
    {
        return $this->file(User::aunt()?->photo_path);
    }

    private function file(?string $path): BinaryFileResponse
    {
        $disk = Media::disk();

        abort_unless($path && $disk->exists($path), 404);

        return response()->file($disk->path($path), [
            'Content-Type' => $disk->mimeType($path) ?: 'application/octet-stream',
            'Cache-Control' => 'private, max-age=86400',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
