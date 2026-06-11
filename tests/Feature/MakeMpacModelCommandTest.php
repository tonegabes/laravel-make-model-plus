<?php

declare(strict_types=1);

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Tester\CommandTester;
use Tonegabes\FilamentMakeModel\Console\MakeMpacModelCommand;

it('creates enum, policy and tests from stubs and forwards command options', function (): void {
    $tempBasePath = base_path('tmp/filament-make-model');
    $files = new Filesystem;

    $files->deleteDirectory($tempBasePath);

    config()->set('filament-make-model.paths.enums', 'tmp/filament-make-model/app/Enums/Permissions');
    config()->set('filament-make-model.paths.policies', 'tmp/filament-make-model/app/Policies');
    config()->set('filament-make-model.paths.tests.unit_enums', 'tmp/filament-make-model/tests/Unit/Enums/Permissions');
    config()->set('filament-make-model.paths.tests.feature_policies', 'tmp/filament-make-model/tests/Feature/Policies');
    config()->set('filament-make-model.filament.panel', 'admin');
    config()->set('filament-make-model.filament.record_title_attribute', 'id');

    $command = new class ($files) extends MakeMpacModelCommand
    {
        /**
         * @var array<string, array<string, mixed>>
         */
        public array $callHistory = [];

        /**
         * Call another console command.
         *
         * @param  string  $command
         * @param  array<string, mixed>  $arguments
         */
        public function call($command, array $arguments = []): int
        {
            $this->callHistory[(string) $command] = $arguments;

            return Command::SUCCESS;
        }
    };

    $command->setLaravel(app());

    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        'name' => 'Evento',
        '--resource' => 'eventos',
        '--migration' => true,
        '--factory' => true,
        '--seed' => true,
        '--panel' => 'painel-custom',
        '--force' => true,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($command->callHistory)->toHaveKeys(['make:model', 'make:filament-resource'])
        ->and($command->callHistory['make:model'])->toMatchArray([
            'name' => 'Evento',
            '--no-interaction' => true,
            '--migration' => true,
            '--factory' => true,
            '--seed' => true,
            '--force' => true,
        ])
        ->and($command->callHistory['make:filament-resource'])->toMatchArray([
            'model' => 'Evento',
            '--panel' => 'painel-custom',
            '--record-title-attribute' => 'id',
            '--view' => true,
            '--no-interaction' => true,
            '--force' => true,
        ]);

    $enumPath = base_path('tmp/filament-make-model/app/Enums/Permissions/EventoPermissions.php');
    $policyPath = base_path('tmp/filament-make-model/app/Policies/EventoPolicy.php');
    $enumTestPath = base_path('tmp/filament-make-model/tests/Unit/Enums/Permissions/EventoPermissionsTest.php');
    $policyTestPath = base_path('tmp/filament-make-model/tests/Feature/Policies/EventoPolicyTest.php');

    expect($files->exists($enumPath))->toBeTrue()
        ->and($files->exists($policyPath))->toBeTrue()
        ->and($files->exists($enumTestPath))->toBeTrue()
        ->and($files->exists($policyTestPath))->toBeTrue()
        ->and($files->get($enumTestPath))->toContain('toHaveCount(10)');

    $files->deleteDirectory($tempBasePath);
});

it('skips filament resource generation when --no-filament is enabled', function (): void {
    $tempBasePath = base_path('tmp/filament-make-model-no-filament');
    $files = new Filesystem;

    $files->deleteDirectory($tempBasePath);

    config()->set('filament-make-model.paths.enums', 'tmp/filament-make-model-no-filament/app/Enums/Permissions');
    config()->set('filament-make-model.paths.policies', 'tmp/filament-make-model-no-filament/app/Policies');
    config()->set('filament-make-model.paths.tests.unit_enums', 'tmp/filament-make-model-no-filament/tests/Unit/Enums/Permissions');
    config()->set('filament-make-model.paths.tests.feature_policies', 'tmp/filament-make-model-no-filament/tests/Feature/Policies');

    $command = new class ($files) extends MakeMpacModelCommand
    {
        /**
         * @var array<string, array<string, mixed>>
         */
        public array $callHistory = [];

        /**
         * Call another console command.
         *
         * @param  string  $command
         * @param  array<string, mixed>  $arguments
         */
        public function call($command, array $arguments = []): int
        {
            $this->callHistory[(string) $command] = $arguments;

            return Command::SUCCESS;
        }
    };

    $command->setLaravel(app());

    $tester = new CommandTester($command);

    $exitCode = $tester->execute([
        'name' => 'Lote',
        '--resource' => 'lotes',
        '--no-filament' => true,
        '--force' => true,
    ]);

    expect($exitCode)->toBe(Command::SUCCESS)
        ->and($command->callHistory)->toHaveKey('make:model')
        ->and($command->callHistory)->not->toHaveKey('make:filament-resource');

    $enumPath = base_path('tmp/filament-make-model-no-filament/app/Enums/Permissions/LotePermissions.php');
    $policyPath = base_path('tmp/filament-make-model-no-filament/app/Policies/LotePolicy.php');

    expect($files->exists($enumPath))->toBeTrue()
        ->and($files->exists($policyPath))->toBeTrue();

    $files->deleteDirectory($tempBasePath);
});
