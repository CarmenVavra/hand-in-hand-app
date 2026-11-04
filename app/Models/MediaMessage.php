<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Eine Nachricht zwischen Tante (sender_id = Benutzer) und Kind (sender_id = NULL).
 * is_read bedeutet immer: der Empfänger hat sie gesehen.
 *
 * @property int $id
 * @property int|null $sender_id
 * @property string $type
 * @property string|null $title
 * @property string $file_path
 * @property string|null $thumbnail_path
 * @property bool $is_read
 * @property Carbon|null $created_at
 */
class MediaMessage extends Model
{
    public const TYPE_VIDEO = 'video';

    public const TYPE_AUDIO = 'audio';

    protected $fillable = [
        'sender_id', 'type', 'title', 'file_path', 'thumbnail_path', 'caption', 'is_read',
    ];

    protected function casts(): array
    {
        return ['is_read' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::deleting(function (MediaMessage $message): void {
            Media::deleteFiles($message->file_path, $message->thumbnail_path);
        });
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Von Tante an das Kind.
     *
     * @param  Builder<MediaMessage>  $query
     */
    public function scopeFromAunt(Builder $query): void
    {
        $query->whereNotNull('sender_id');
    }

    /**
     * Vom Kind an Tante.
     *
     * @param  Builder<MediaMessage>  $query
     */
    public function scopeFromChild(Builder $query): void
    {
        $query->whereNull('sender_id');
    }

    /** @param  Builder<MediaMessage>  $query */
    public function scopeUnread(Builder $query): void
    {
        $query->where('is_read', false);
    }

    public function isFromChild(): bool
    {
        return $this->sender_id === null;
    }

    public function isAudio(): bool
    {
        return $this->type === self::TYPE_AUDIO;
    }

    public function url(): string
    {
        return route('media.message', $this);
    }

    public function displayTitle(): string
    {
        return $this->title ?: ($this->isAudio() ? 'Sprachnachricht' : 'Video').' '.$this->created_at?->format('d.m.');
    }

    public function markRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill(['is_read' => true])->save();
        }
    }

    public static function hasUnreadForChild(): bool
    {
        return static::query()->fromAunt()->unread()->exists();
    }

    public static function unreadFromChildCount(): int
    {
        return static::query()->fromChild()->unread()->count();
    }
}
