<?php

declare(strict_types=1);

namespace Tonegabes\FilamentMakeModel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tonegabes\FilamentMakeModel\FilamentMakeModelServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * Register package service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            FilamentMakeModelServiceProvider::class,
        ];
    }
}
