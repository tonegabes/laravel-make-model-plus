<?php

declare(strict_types=1);

namespace Tonegabes\LaravelMakeModelPlus\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Tonegabes\LaravelMakeModelPlus\LaravelMakeModelPlusServiceProvider;

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
            LaravelMakeModelPlusServiceProvider::class,
        ];
    }
}
