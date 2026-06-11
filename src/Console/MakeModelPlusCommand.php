<?php

declare(strict_types=1);

namespace Tonegabes\LaravelMakeModelPlus\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeModelPlusCommand extends Command
{
    protected $signature = 'make:model-plus
                            {name : Nome do model, ex: Evento}
                            {--resource= : Base das permissions, ex: eventos}
                            {--panel= : ID do painel Filament, ex: admin}
                            {--no-filament : Pula a geração do Filament Resource}
                            {--migration : Criar migration}
                            {--factory : Criar factory}
                            {--seed : Criar seeder}
                            {--force : Sobrescrever arquivos existentes}'
    ;

    protected $description = 'Cria um model com enum de permissions, policy, testes e Filament Resource com view page';

    public function __construct(
        private readonly Filesystem $files,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $modelName = Str::studly($this->argument('name'));
        $resourceBase = $this->resolveResourceBase($modelName);
        $modelVariable = Str::camel($modelName);

        $modelCommandOptions = [
            'name' => $modelName,
            '--no-interaction' => true,
        ];

        if ($this->option('migration')) {
            $modelCommandOptions['--migration'] = true;
        }

        if ($this->option('factory')) {
            $modelCommandOptions['--factory'] = true;
        }

        if ($this->option('seed')) {
            $modelCommandOptions['--seed'] = true;
        }

        if ($this->option('force')) {
            $modelCommandOptions['--force'] = true;
        }

        $this->info("Criando model {$modelName}...");

        if ($this->call('make:model', $modelCommandOptions) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->createPermissionEnum($modelName, $resourceBase);
        $this->createPolicy($modelName, $modelVariable, $resourceBase);
        $this->createPermissionEnumTest($modelName, $resourceBase);
        $this->createPolicyTest($modelName, $modelVariable, $resourceBase);

        if ($this->shouldSkipFilamentResource()) {
            $this->newLine();
            $this->info("make:mpac-model finalizado para {$modelName}.");

            return self::SUCCESS;
        }

        $this->info("Criando Filament Resource {$modelName} com página de visualização...");

        $resourceCommandOptions = [
            'model' => $modelName,
            '--panel' => $this->resolvePanel(),
            '--record-title-attribute' => $this->resolveRecordTitleAttribute(),
            '--view' => true,
            '--no-interaction' => true,
        ];

        if ($this->option('force')) {
            $resourceCommandOptions['--force'] = true;
        }

        if ($this->call('make:filament-resource', $resourceCommandOptions) !== self::SUCCESS) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("make:mpac-model finalizado para {$modelName}.");

        return self::SUCCESS;
    }

    /**
     * Determine whether the Filament resource generation must be skipped.
     */
    private function shouldSkipFilamentResource(): bool
    {
        return (bool) $this->option('no-filament');
    }

    /**
     * Resolve the resource base used by permission keys.
     */
    private function resolveResourceBase(string $modelName): string
    {
        $resourceOption = (string) ($this->option('resource') ?? '');

        if ($resourceOption !== '') {
            return Str::of($resourceOption)
                ->trim()
                ->lower()
                ->replace(' ', '_')
                ->toString();
        }

        return Str::of(Str::snake($modelName))
            ->replace(' ', '_')
            ->plural()
            ->toString();
    }

    /**
     * Resolve the target Filament panel.
     */
    private function resolvePanel(): string
    {
        $panelOption = (string) ($this->option('panel') ?? '');

        if ($panelOption !== '') {
            return $panelOption;
        }

        return (string) config('laravel-make-model-plus.filament.panel', 'admin');
    }

    /**
     * Resolve the record title attribute for generated resources.
     */
    private function resolveRecordTitleAttribute(): string
    {
        return (string) config('laravel-make-model-plus.filament.record_title_attribute', 'id');
    }

    /**
     * Build all placeholders used by stubs.
     *
     * @return array<string, string>
     */
    private function buildStubReplacements(string $modelName, string $modelVariable, string $resourceBase): array
    {
        $permissionModelFqcn = $this->resolvePermissionModelFqcn();
        $permissionModelClass = class_basename($permissionModelFqcn);
        $userModelFqcn = $this->resolveUserModelFqcn();
        $userModelClass = class_basename($userModelFqcn);
        $appNamespace = trim((string) config('laravel-make-model-plus.app_namespace', 'App'), '\\');

        return [
            '{{ AppNamespace }}' => $appNamespace,
            '{{ EnumClass }}' => "{$modelName}Permissions",
            '{{ ModelClass }}' => $modelName,
            '{{ modelVariable }}' => $modelVariable,
            '{{ PermissionEnumClass }}' => "{$modelName}Permissions",
            '{{ PermissionModel }}' => $permissionModelClass,
            '{{ PermissionModelFqcn }}' => $permissionModelFqcn,
            '{{ UserModel }}' => $userModelClass,
            '{{ UserModelFqcn }}' => $userModelFqcn,
            '{{ resource }}' => $resourceBase,
        ];
    }

    /**
     * Resolve the configured User model FQCN.
     */
    private function resolveUserModelFqcn(): string
    {
        return ltrim((string) config('laravel-make-model-plus.models.user', 'App\\Models\\User'), '\\');
    }

    /**
     * Resolve the configured Permission model FQCN.
     */
    private function resolvePermissionModelFqcn(): string
    {
        $model = config('laravel-make-model-plus.models.permission');

        if (is_string($model) && $model !== '') {
            return ltrim($model, '\\');
        }

        return ltrim((string) config('permission.models.permission', 'Spatie\\Permission\\Models\\Permission'), '\\');
    }

    /**
     * Create the enum file.
     */
    private function createPermissionEnum(string $modelName, string $resourceBase): void
    {
        $targetPath = $this->resolveConfiguredPath('paths.enums', "{$modelName}Permissions.php");
        $replacements = $this->buildStubReplacements($modelName, Str::camel($modelName), $resourceBase);

        $this->writeFromStub(
            stubPath: 'permission-enum.stub',
            targetPath: $targetPath,
            replacements: $replacements,
        );
    }

    /**
     * Create the policy file.
     */
    private function createPolicy(string $modelName, string $modelVariable, string $resourceBase): void
    {
        $targetPath = $this->resolveConfiguredPath('paths.policies', "{$modelName}Policy.php");
        $replacements = $this->buildStubReplacements($modelName, $modelVariable, $resourceBase);

        $this->writeFromStub(
            stubPath: 'policy.stub',
            targetPath: $targetPath,
            replacements: $replacements,
        );
    }

    /**
     * Create the enum test file.
     */
    private function createPermissionEnumTest(string $modelName, string $resourceBase): void
    {
        $targetPath = $this->resolveConfiguredPath(
            'paths.tests.unit_enums',
            "{$modelName}PermissionsTest.php",
        );
        $replacements = $this->buildStubReplacements($modelName, Str::camel($modelName), $resourceBase);

        $this->writeFromStub(
            stubPath: 'permission-enum-test.stub',
            targetPath: $targetPath,
            replacements: $replacements,
        );
    }

    /**
     * Create the policy test file.
     */
    private function createPolicyTest(string $modelName, string $modelVariable, string $resourceBase): void
    {
        $targetPath = $this->resolveConfiguredPath('paths.tests.feature_policies', "{$modelName}PolicyTest.php");
        $replacements = $this->buildStubReplacements($modelName, $modelVariable, $resourceBase);

        $this->writeFromStub(
            stubPath: 'policy-test.stub',
            targetPath: $targetPath,
            replacements: $replacements,
        );
    }

    /**
     * Resolve a configurable relative path to an absolute path.
     */
    private function resolveConfiguredPath(string $configKey, string $fileName): string
    {
        $relativePath = trim((string) config("laravel-make-model-plus.{$configKey}"), '\\/');

        return base_path($relativePath . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * Resolve a stub file path.
     */
    private function resolveStubPath(string $stubPath): string
    {
        $configuredStubPath = config('laravel-make-model-plus.stubs.path');

        if (is_string($configuredStubPath) && $configuredStubPath !== '') {
            return rtrim($configuredStubPath, '\\/') . DIRECTORY_SEPARATOR . $stubPath;
        }

        return __DIR__ . '/../../stubs/' . $stubPath;
    }

    /**
     * Write a file from a stub and replacement placeholders.
     *
     * @param array<string, string> $replacements
     */
    private function writeFromStub(string $stubPath, string $targetPath, array $replacements): void
    {
        if (! $this->option('force') && $this->files->exists($targetPath)) {
            $this->warn("Arquivo já existe, pulando: {$targetPath}");

            return;
        }

        $stubContent = $this->files->get($this->resolveStubPath($stubPath));
        $content = str_replace(array_keys($replacements), array_values($replacements), $stubContent);

        $this->files->ensureDirectoryExists(dirname($targetPath));
        $this->files->put($targetPath, $content);

        $this->line("Arquivo criado: {$targetPath}");
    }
}
