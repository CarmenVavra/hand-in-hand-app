<?php

namespace Database\Seeders;

use App\Models\MediaMessage;
use App\Models\PictureBook;
use App\Models\User;
use App\Support\Media;
use Illuminate\Database\Seeder;

/**
 * Nur für die lokale Entwicklung: ein Bilderbuch mit bunten Platzhalter-Fotos und Sinus-Tönen
 * sowie ein paar Postkasten-Einträge. Aufruf: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command->error('DemoSeeder läuft nicht in Produktion.');

            return;
        }

        $this->call(DatabaseSeeder::class);

        $book = PictureBook::query()->firstOrCreate(['title' => 'Tiere im Garten']);

        if ($book->pages()->doesntExist()) {
            foreach ([['Katze', 'ff4f9a', 440], ['Garten', '0e8f55', 523], ['Bagger', '2d6bff', 330], ['Hund', 'e8590c', 392]] as [$label, $color, $hz]) {
                $image = 'books/images/demo-'.strtolower($label).'.jpg';
                $audio = 'books/audios/demo-'.strtolower($label).'.wav';
                Media::disk()->put($image, $this->placeholderImage($label, $color));
                Media::disk()->put($audio, $this->sineWav($hz));
                $book->addPage($image, $audio, $label);
            }
        }

        $aunt = User::aunt();

        if (MediaMessage::query()->doesntExist()) {
            foreach (['Der Bagger', 'Hund im Park', 'Gute Nacht!'] as $title) {
                Media::disk()->put($path = 'messages/from-aunt/demo-'.md5($title).'.mp4', '');
                MediaMessage::query()->create(['sender_id' => $aunt?->id, 'type' => 'video', 'title' => $title, 'file_path' => $path]);
            }

            Media::disk()->put($path = 'messages/from-child/demo.webm', '');
            MediaMessage::query()->create(['sender_id' => null, 'type' => 'video', 'file_path' => $path]);
        }
    }

    private function placeholderImage(string $label, string $hex): string
    {
        $im = imagecreatetruecolor(1200, 900);
        $fill = imagecolorallocate($im, hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2)));
        imagefill($im, 0, 0, $fill);
        imagefilledellipse($im, 600, 450, 420, 420, (int) imagecolorallocate($im, 255, 210, 63));
        imagestring($im, 5, 40, 40, 'FOTO: '.$label, (int) imagecolorallocate($im, 255, 255, 255));

        ob_start();
        imagejpeg($im, null, 80);

        return (string) ob_get_clean();
    }

    private function sineWav(int $hz, float $seconds = 2.0): string
    {
        $rate = 22050;
        $samples = (int) ($rate * $seconds);
        $data = '';
        for ($i = 0; $i < $samples; $i++) {
            $fade = min(1, $i / 800, ($samples - $i) / 800);
            $data .= pack('v', (int) (sin(2 * M_PI * $hz * $i / $rate) * 9000 * $fade) & 0xFFFF);
        }

        return 'RIFF'.pack('V', 36 + strlen($data)).'WAVEfmt '.pack('VvvVVvv', 16, 1, 1, $rate, $rate * 2, 2, 16).'data'.pack('V', strlen($data)).$data;
    }
}
