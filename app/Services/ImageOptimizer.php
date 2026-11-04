<?php

namespace App\Services;

use App\Support\Media;
use Illuminate\Http\UploadedFile;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Verkleinert Handy-Fotos (max. 1600 px, JPEG), korrigiert die Ausrichtung (EXIF)
 * und legt sie im Medien-Speicher ab. Fällt ohne GD auf das unveränderte Speichern zurück.
 */
class ImageOptimizer
{
    public const MAX_EDGE = 1600;

    public function store(UploadedFile|TemporaryUploadedFile $file, string $directory): string
    {
        $optimized = $this->optimize($file->getRealPath());

        if ($optimized === null) {
            return (string) Media::disk()->putFile($directory, $file);
        }

        $path = trim($directory, '/').'/'.bin2hex(random_bytes(16)).'.jpg';
        Media::disk()->put($path, $optimized);

        return $path;
    }

    private function optimize(string|false $realPath): ?string
    {
        if ($realPath === false || ! function_exists('imagecreatefromstring')) {
            return null;
        }

        $raw = @file_get_contents($realPath);
        $image = $raw === false ? false : @imagecreatefromstring($raw);

        if ($image === false) {
            return null;
        }

        $image = $this->orient($image, $realPath);

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, self::MAX_EDGE / max($width, $height));

        if ($scale < 1) {
            $resized = imagescale($image, max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
            if ($resized !== false) {
                $image = $resized;
            }
        }

        // Transparente PNGs auf weißem Grund flachrechnen (JPEG kennt keine Transparenz)
        $flat = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($flat, 0, 0, (int) imagecolorallocate($flat, 255, 255, 255));
        imagecopy($flat, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($flat, null, 82);

        return (string) ob_get_clean();
    }

    private function orient(\GdImage $image, string $realPath): \GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($realPath);
        $orientation = is_array($exif) ? ($exif['Orientation'] ?? 1) : 1;

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        return $rotated === false ? $image : $rotated;
    }
}
