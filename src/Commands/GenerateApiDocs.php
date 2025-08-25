<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Alagiesinghateh\LaravelApiDocGenerator\Services\ApiAnnotationGenerator;
use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGenerator;
use Illuminate\Console\Command;

class GenerateApiDocs extends Command
{
    protected $signature = 'singhateh:generate {--dry-run : Show what would be annotated without writing}';

    protected $description = 'Generate API documentation from controller annotations';

    protected $generator;

    protected $annotationGenerator;

    public function __construct(ApiDocGenerator $generator, ApiAnnotationGenerator $annotationGenerator)
    {
        parent::__construct();
        $this->generator = $generator;
        $this->annotationGenerator = $annotationGenerator;
    }

    public function handle(): void
    {
        $totalAnnotated = 0;

        // First run: Generate annotations and documentation
        $this->info('🔹 Generating API documentation...');
        $firstRunAnnotated = $this->annotateControllers(false);
        $totalAnnotated += $firstRunAnnotated;

        // Generate documentation
        $docs = $this->generator->generate();
        $this->info("📝 {$firstRunAnnotated} controller methods annotated.");
        $this->info('✅ API documentation generated successfully!');
        $this->info('📊 Found '.count($docs).' API groups.');
        $this->info('💾 Documentation saved to: '.config('api-doc-generator.output_dir').'/api-docs.json');

        $this->info("✅ Final completion. Total methods annotated: {$totalAnnotated}");
    }

    protected function annotateControllers(bool $isCrossCheck = false): int
    {
        $controllerPaths = config('api-doc-generator.controller_paths', [app_path('Http/Controllers/API')]);

        if ($this->option('dry-run')) {
            $this->info('Dry run: Would process controllers in: '.implode(', ', $controllerPaths));

            return 0;
        }

        return $this->annotationGenerator->annotateControllers($controllerPaths, $isCrossCheck);
    }
}
