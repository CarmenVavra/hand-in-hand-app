<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property int $picture_book_id
 * @property string $image_path
 * @property string $audio_path
 * @property string|null $label
 * @property int $order_column
 */
class PictureBookPage extends Model
{
    protected $fillable = ['picture_book_id', 'image_path', 'audio_path', 'label', 'order_column'];

    protected static function booted(): void
    {
        static::deleting(function (PictureBookPage $page): void {
            Media::deleteFiles($page->image_path, $page->audio_path);
        });
    }

    /** @return BelongsTo<PictureBook, $this> */
    public function book(): BelongsTo
    {
        return $this->belongsTo(PictureBook::class, 'picture_book_id');
    }

    public function imageUrl(): string
    {
        return route('media.page.image', $this);
    }

    public function audioUrl(): string
    {
        return route('media.page.audio', $this);
    }

    /** Tauscht die Reihenfolge mit der Nachbarseite ($direction: -1 = hoch, +1 = runter). */
    public function move(int $direction): void
    {
        DB::transaction(function () use ($direction) {
            $query = static::query()->where('picture_book_id', $this->picture_book_id);

            $neighbour = $direction < 0
                ? $query->where('order_column', '<', $this->order_column)->orderByDesc('order_column')->first()
                : $query->where('order_column', '>', $this->order_column)->orderBy('order_column')->first();

            if ($neighbour === null) {
                return;
            }

            [$mine, $theirs] = [$this->order_column, $neighbour->order_column];
            $this->update(['order_column' => $theirs]);
            $neighbour->update(['order_column' => $mine]);
        });
    }

    /** Löscht die Seite samt Dateien und nummeriert die übrigen Seiten lückenlos neu (1, 2, 3 …). */
    public function deleteAndReindex(): void
    {
        DB::transaction(function () {
            $bookId = $this->picture_book_id;
            $this->delete();

            static::query()->where('picture_book_id', $bookId)->orderBy('order_column')->orderBy('id')->get()
                ->each(fn (PictureBookPage $page, int $index) => $page->update(['order_column' => $index + 1]));
        });
    }
}
