# Filament Make Model

[![Latest Version on Packagist](https://img.shields.io/packagist/v/tonegabes/laravel-make-model-plus.svg?style=flat-square)](https://packagist.org/packages/tonegabes/laravel-make-model-plus)
[![Total Downloads](https://img.shields.io/packagist/dt/tonegabes/laravel-make-model-plus.svg?style=flat-square)](https://packagist.org/packages/tonegabes/laravel-make-model-plus)
[![License](https://img.shields.io/packagist/l/tonegabes/laravel-make-model-plus.svg?style=flat-square)](https://packagist.org/packages/tonegabes/laravel-make-model-plus)

`tonegabes/laravel-make-model-plus` implement additional features envolving Filament resources and permissions enums.

## Requirements

- PHP 8.3+
- Laravel 12 or 13
- Filament 5

## Installation

```bash
composer require tonegabes/laravel-make-model-plus
```

Optional: publish config and stubs.

```bash
php artisan vendor:publish --tag=laravel-make-model-plus-config
php artisan vendor:publish --tag=laravel-make-model-plus-stubs
```

## Usage

```bash
php artisan make:model-plus Evento --resource=eventos
```

### Available options

- `--resource=`: Base permission key (for example, `eventos`)
- `--panel=`: Filament panel id (defaults to `admin`)
- `--no-filament`: Skip Filament resource generation
- `--migration`
- `--factory`
- `--seed`
- `--force`

### Generated files

- Model via `make:model`
- `app/Enums/Permissions/{Model}Permissions.php`
- `app/Policies/{Model}Policy.php`
- `tests/Unit/Enums/Permissions/{Model}PermissionsTest.php`
- `tests/Feature/Policies/{Model}PolicyTest.php`
- Filament Resource with `View` page via `make:filament-resource`

When `--no-filament` is used, only model/domain scaffolding is generated.

## Release checklist (`v1.0.0`)

- Ensure package tests are green: `vendor/bin/pest --compact`
- Ensure code style is clean: `vendor/bin/pint --dirty --format agent`
- Update `CHANGELOG.md` (or release notes) with the final scope
- Create git tag: `v1.0.0`
- Push tag and trigger Packagist update
