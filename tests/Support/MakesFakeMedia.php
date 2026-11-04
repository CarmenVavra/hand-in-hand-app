<?php

namespace Tests\Support;

use Illuminate\Http\UploadedFile;

/**
 * Test-Dateien mit ausdrücklich gemeldetem MIME-Typ
 * (Livewire verwendet in Tests den gemeldeten Typ, im Betrieb den aus dem Dateiinhalt erkannten).
 */
trait MakesFakeMedia
{
    protected function fakeVideo(string $name = 'nachricht.mp4', int $kilobytes = 16): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'video/mp4');
    }

    protected function fakeAudio(string $name = 'stimme.wav', int $kilobytes = 8): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, 'audio/wav');
    }

    protected function fakeImage(string $name = 'katze.jpg', int $width = 800, int $height = 600): UploadedFile
    {
        return UploadedFile::fake()->image($name, $width, $height);
    }
}
