<?php

use App\Http\Controllers\ChildAccessController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\MediaController;
use App\Livewire\Admin;
use App\Livewire\Child;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/home');

/*
|--------------------------------------------------------------------------
| Freischaltung des Kinder-Handys (nur wenn HIH_CHILD_TOKEN gesetzt ist)
|--------------------------------------------------------------------------
*/
Route::get('/zugang/{token}', ChildAccessController::class)->middleware('throttle:10,1')->name('child.access');

/*
|--------------------------------------------------------------------------
| Kinderbereich: Hauptmenü, Aufnahme, Postkasten, Bilderbuch
|--------------------------------------------------------------------------
*/
Route::middleware('child.access')->group(function () {
    Route::prefix('home')->name('child.')->group(function () {
        Route::get('/', Child\Dashboard::class)->name('dashboard');
        Route::get('/nachricht', Child\Record::class)->name('record');
        Route::get('/postkasten', Child\Mailbox::class)->name('mailbox');
        Route::get('/bilderbuch', Child\BookSelect::class)->name('book.select');
        Route::get('/bilderbuch/{book}', Child\BookViewer::class)->name('book.view');
    });

    // Medien (kein öffentlicher /storage-Link, Auslieferung mit Zugriffsprüfung)
    Route::prefix('medien')->name('media.')->group(function () {
        Route::get('/nachricht/{message}', [MediaController::class, 'message'])->name('message');
        Route::get('/seite/{page}/bild', [MediaController::class, 'pageImage'])->name('page.image');
        Route::get('/seite/{page}/audio', [MediaController::class, 'pageAudio'])->name('page.audio');
        Route::get('/profil', [MediaController::class, 'profile'])->name('profile');
    });
});

/*
|--------------------------------------------------------------------------
| Admin-Bereich (Tante)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', Admin\Login::class)->name('login');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/', Admin\Home::class)->name('home');
        Route::post('/logout', LogoutController::class)->name('logout');

        Route::get('/buch/{book}', Admin\BookShow::class)->name('book.show');
        Route::get('/buch/{book}/neu', Admin\CreatePage::class)->name('page.create');

        Route::get('/nachrichten', Admin\Messages::class)->name('messages');
        Route::get('/nachrichten/neu', Admin\SendMessage::class)->name('messages.create');

        Route::get('/einstellungen', Admin\Settings::class)->name('settings');
    });
});

/*
|--------------------------------------------------------------------------
| Rechtliches & PWA-Offline-Fallback
|--------------------------------------------------------------------------
*/
Route::view('/impressum', 'legal.impressum')->name('legal.impressum');
Route::view('/datenschutz', 'legal.datenschutz')->name('legal.privacy');
Route::view('/offline', 'offline')->name('pwa.offline');
