<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $title
 * @property string|null $cover_image
 * @property Collection<int, PictureBookPage> $pages
 */
class PictureBook extends Model
{
    protected $fillable = ['title', 'cover_image'];

    protected static function booted(): void
    {
        static::deleting(function (PictureBook $book): void {
            // DB-Cascade löst keine Model-Events aus – Dateien daher hier einsammeln.
            $book->pages()->get()->each->delete();
            Media::deleteFiles($book->cover_image);
        });
    }

    /** @return HasMany<PictureBookPage, $this> */
    public function pages(): HasMany
    {
        return $this->hasMany(PictureBookPage::class)->orderBy('order_column')->orderBy('id');
    }

    public function addPage(string $imagePath, string $audioPath, ?string $label = null): PictureBookPage
    {
        return DB::transaction(function () use ($imagePath, $audioPath, $label) {
            $next = (int) $this->pages()->reorder()->max('order_column') + 1;

            return $this->pages()->create([
                'image_path' => $imagePath,
                'audio_path' => $audioPath,
                'label' => $label,
                'order_column' => $next,
            ]);
        });
    }

    /** Titelbild: das Foto der ersten Seite (pages muss geladen sein). */
    public function coverUrl(): ?string
    {
        $first = $this->pages->first();

        return $first ? route('media.page.image', $first) : null;
    }
}
