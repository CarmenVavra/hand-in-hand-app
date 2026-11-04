<?php

namespace App\Providers;

use App\Http\Middleware\EnsureChildAccess;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // N+1-Abfragen fallen in Entwicklung/Tests sofort auf.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Der Zugangsschutz gilt auch für Livewire-Aktionen (Upload, Speichern), nicht nur für das erste Laden.
        Livewire::addPersistentMiddleware([EnsureChildAccess::class]);

        if ($this->app->isProduction()) {
            URL::forceHttps();
        }
    }
}
