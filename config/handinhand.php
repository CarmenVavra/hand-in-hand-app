<?php

return [

    /*
    | Anzeigename, falls (noch) kein Admin-Benutzer existiert.
    */
    'aunt_name' => env('HIH_AUNT_NAME', 'Tante Carmen'),

    /*
    | Zugangs-Token für das Kinder-Handy. Ist er gesetzt, muss das Gerät einmalig
    | /zugang/<token> aufrufen (setzt ein langlebiges Cookie). Leer = Kinderbereich offen.
    */
    'child_token' => env('HIH_CHILD_TOKEN'),

    // Zugangsdaten für `php artisan db:seed` / `handinhand:admin` (nur beim Einrichten nötig)
    'admin' => [
        'name' => env('ADMIN_NAME'),
        'email' => env('ADMIN_EMAIL'),
        'password' => env('ADMIN_PASSWORD'),
    ],

    'limits' => [
        'video_mb' => (int) env('HIH_MAX_VIDEO_MB', 30),
        'audio_mb' => (int) env('HIH_MAX_AUDIO_MB', 5),
        'image_mb' => (int) env('HIH_MAX_IMAGE_MB', 12),
    ],

    // Maximale Dauer einer Aufnahme im Browser (hält Videos unter dem Upload-Limit).
    'max_record_seconds' => (int) env('HIH_MAX_RECORD_SECONDS', 90),

    // Intervall, in dem der Kinder-Bildschirm bzw. die Admin-Ansicht nach Neuem fragt.
    'poll_seconds' => (int) env('HIH_POLL_SECONDS', 20),

    // Speicher-Disk für alle Medien (privat, Auslieferung über MediaController).
    'disk' => env('HIH_MEDIA_DISK', 'local'),

    // Farben für Video-Kacheln im Postkasten (reihum).
    'tile_colors' => ['#EC3013', '#0E8F55', '#7A3BFF', '#E8590C', '#2D6BFF'],

    // Hintergrundfarben der Bilderbuch-Seiten (reihum).
    'page_colors' => ['#FF4F9A', '#0E8F55', '#2D6BFF', '#E8590C', '#7A3BFF'],
];
