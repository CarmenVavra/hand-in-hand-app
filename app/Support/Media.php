<?php

namespace App\Support;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

/**
 * Zentrale Stelle für Medien-Speicher und Upload-Regeln.
 */
class Media
{
    /** MIME-Typen, die Browser (Chrome/Firefox/Safari) beim Aufnehmen bzw. Handys beim Filmen erzeugen. */
    public const VIDEO_MIMES = [
        'video/webm', 'video/mp4', 'video/quicktime', 'video/x-m4v', 'video/ogg', 'video/3gpp',
    ];

    public const AUDIO_MIMES = [
        'audio/webm', 'audio/ogg', 'audio/mp4', 'audio/x-m4a', 'audio/m4a', 'audio/mpeg', 'audio/aac',
        'audio/wav', 'audio/x-wav', 'video/webm', 'video/mp4', 'application/ogg',
    ];

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(config('handinhand.disk'));
    }

    /**
     * @return list<string>
     */
    public static function videoRules(): array
    {
        return ['required', 'file', 'mimetypes:'.implode(',', self::VIDEO_MIMES), 'max:'.(config('handinhand.limits.video_mb') * 1024)];
    }

    /**
     * Video ODER Audio (Tante schickt auch Sprachnachrichten).
     *
     * @return list<string>
     */
    public static function videoOrAudioRules(): array
    {
        return [
            'required', 'file',
            'mimetypes:'.implode(',', array_unique([...self::VIDEO_MIMES, ...self::AUDIO_MIMES])),
            'max:'.(config('handinhand.limits.video_mb') * 1024),
        ];
    }

    /**
     * @return list<string>
     */
    public static function audioRules(): array
    {
        return ['required', 'file', 'mimetypes:'.implode(',', self::AUDIO_MIMES), 'max:'.(config('handinhand.limits.audio_mb') * 1024)];
    }

    /**
     * @return list<string>
     */
    public static function imageRules(bool $required = true): array
    {
        return [$required ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:'.(config('handinhand.limits.image_mb') * 1024)];
    }

    public static function deleteFiles(?string ...$paths): void
    {
        $paths = array_values(array_filter($paths));

        if ($paths !== []) {
            self::disk()->delete($paths);
        }
    }
}
