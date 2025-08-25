<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Alagiesinghateh\LaravelApiDocGenerator\Services\ApiAnnotationGenerator;
use Illuminate\Console\Command;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class RegenerateApiAnnotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'singhateh:regenerate 
                            {--path= : Specific controller path to process (relative to app path)}
                            {--cross-check : Only check which methods need annotations without modifying files}
                            {--force : Force regeneration even if annotations already exist}
                            {--dry-run : Show what would be changed without actually modifying files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force regenerate API annotations for controllers';

    protected $generator;

    /**
     * Create a new command instance.
     */
    public function __construct(Router $router)
    {
        parent::__construct();
        $this->generator = new ApiAnnotationGenerator($router);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get controller paths from config or use default
        $controllerPaths = $this->getControllerPaths();

        if (empty($controllerPaths)) {
            $this->error('No valid controller paths found!');
            $this->line('Please check your config or specify a path with --path option');

            return 1;
        }

        $isCrossCheck = $this->option('cross-check');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        $this->info('Starting API annotation regeneration...');
        $this->line('Processing paths: '.implode(', ', $controllerPaths));

        if ($isCrossCheck) {
            $this->info('Running in cross-check mode (no files will be modified)');
        }

        if ($dryRun) {
            $this->info('Dry run mode - no files will be modified');
        }

        if ($force) {
            $this->warn('Force mode enabled - will regenerate all annotations');
            $this->generator->setForceMode(true);
        }

        if ($dryRun) {
            $this->generator->setDryRun(true);
        }

        $annotatedCount = $this->generator->annotateControllers($controllerPaths, $isCrossCheck);

        if ($dryRun) {
            $this->info("Dry run complete. Would annotate {$annotatedCount} controller methods.");
        } elseif ($isCrossCheck) {
            $this->info("Cross-check complete. Found {$annotatedCount} methods that need annotations.");
        } else {
            $this->info("Successfully annotated {$annotatedCount} controller methods.");
        }

        $this->info('Annotation regeneration completed!');

        return 0;
    }

    /**
     * Get controller paths to process
     */
    protected function getControllerPaths(): array
    {
        $specificPath = $this->option('path');

        if ($specificPath) {
            // Use the specified path (can be relative to app path or absolute)
            $fullPath = $this->resolvePath($specificPath);

            return [$fullPath];
        }

        // Get paths from config or use defaults
        $configPaths = Config::get('api-doc-generator.controller_paths', [
            app_path('Http/Controllers'),
        ]);

        // Ensure paths exist and are readable
        return array_filter($configPaths, function ($path) {
            return File::exists($path) && File::isReadable($path);
        });
    }

    /**
     * Resolve path to absolute path
     */
    protected function resolvePath(string $path): string
    {
        // If it's already an absolute path
        if (File::exists($path)) {
            return $path;
        }

        // Try relative to app path
        $appPath = app_path($path);
        if (File::exists($appPath)) {
            return $appPath;
        }

        // Try relative to base path
        $basePath = base_path($path);
        if (File::exists($basePath)) {
            return $basePath;
        }

        return $path;
    }
}
