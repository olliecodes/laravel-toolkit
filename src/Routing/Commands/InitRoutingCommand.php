<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Routing\Commands;

use Illuminate\Console\ConfirmableTrait;
use Illuminate\Console\GeneratorCommand;

class InitRoutingCommand extends GeneratorCommand
{
    use ConfirmableTrait;

    protected $signature = 'init:routing {--K|keepRoutesDir : Keep the routes/ directory}';

    protected $description = 'Initialise the toolkit routing functionality.';

    public function handle(): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->warn('This command will overwrite the following:');
        $this->line('Console/Kernel.php');
        $this->line('Providers/BroadcastServiceProvider.php');
        $this->line('Providers/RouteServiceProvider.php');

        $this->warn('It will also create a default routes registrar');

        if (! $this->option('keepRoutesDir')) {
            $this->warn('This command will also delete the routes directory');
        }

        if (! $this->confirm('Are you sure you wish to proceed?')) {
            return self::FAILURE;
        }

        $this->overwriteConsoleKernel();
        $this->overwriteBroadcastServiceProvider();
        $this->overwriteRouteServiceProvider();
        $this->addDefaultRoutes();

        if (! $this->option('keepRoutesDir')) {
            $this->removeRoutesDir();
        }

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        return '';
    }

    private function getReplacement(string $stubName, string $name): string
    {
        $stub = $this->files->get($stubName);
        $this->replaceNamespace($stub, $name);
        return $this->sortImports($stub);
    }

    private function overwriteConsoleKernel(): void
    {
        $this->files->put(
            app_path('Console/Kernel.php'),
            $this->getReplacement(
                __DIR__ . '/../../../resources/replacements/console-kernel.stub',
                'Console\\Kernel'
            )
        );
    }

    private function overwriteBroadcastServiceProvider(): void
    {
        $this->files->put(
            app_path('Providers/BroadcastServiceProvider.php'),
            $this->getReplacement(
                __DIR__ . '/../../../resources/replacements/broadcast-service-provider.stub',
                'Providers\\BroadcastServiceProvider'
            )
        );
    }

    private function addDefaultRoutes(): void
    {
        $this->files->put(
            app_path('Http/Routes/DefaultRoutes.php'),
            $this->getReplacement(
                __DIR__ . '/../../../resources/replacements/default-routes.stub',
                'Http\\Routes\\DefaultRoutes'
            )
        );
    }

    private function overwriteRouteServiceProvider(): void
    {
        $this->files->put(
            app_path('Providers/RouteServiceProvider.php'),
            $this->getReplacement(
                __DIR__ . '/../../../resources/replacements/route-service-provider.stub',
                'Providers\\RouteServiceProvider'
            )
        );
    }

    private function removeRoutesDir(): void
    {
        $this->files->deleteDirectory(base_path('routes'));
    }
}