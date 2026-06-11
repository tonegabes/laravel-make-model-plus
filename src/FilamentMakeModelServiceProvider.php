<?php

declare(strict_types=1);

namespace Tonegabes\FilamentMakeModel;

use Illuminate\Support\ServiceProvider;
use Tonegabes\FilamentMakeModel\Console\MakeMpacModelCommand;

class FilamentMakeModelServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-make-model.php',
            'filament-make-model',
        );
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        $this->commands([
            MakeMpacModelCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/filament-make-model.php' => config_path('filament-make-model.php'),
        ], 'filament-make-model-config');

        $this->publishes([
            __DIR__ . '/../stubs' => base_path('stubs/filament-make-model'),
        ], 'filament-make-model-stubs');
    }
}
