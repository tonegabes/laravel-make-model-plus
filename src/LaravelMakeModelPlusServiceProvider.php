<?php

declare(strict_types=1);

namespace Tonegabes\LaravelMakeModelPlus;

use Illuminate\Support\ServiceProvider;
use Tonegabes\LaravelMakeModelPlus\Console\MakeModelPlusCommand;

class LaravelMakeModelPlusServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-make-model-plus.php',
            'laravel-make-model-plus',
        );
    }

    public function boot(): void
    {
        $this->commands([
            MakeModelPlusCommand::class,
        ]);

        $this->publishes([
            __DIR__ . '/../config/laravel-make-model-plus.php' => config_path('laravel-make-model-plus.php'),
        ], 'laravel-make-model-plus-config');

        $this->publishes([
            __DIR__ . '/../stubs' => base_path('stubs/laravel-make-model-plus'),
        ], 'laravel-make-model-plus-stubs');
    }
}
