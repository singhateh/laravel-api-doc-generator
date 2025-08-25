<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Commands;

use Alagiesinghateh\LaravelApiDocGenerator\Services\ApiAnnotationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;

class ForceRegenerateAnnotations extends Command
{
    protected $signature = 'singhateh:force-regenerate 
                            {--dry-run : Show what would be regenerated without writing}
                            {--skip-backup : Skip creating backup files}
                            {--only-missing : Only add annotations to methods that dont have them}
                            {--restore-backups : Restore all last backups before regenerating}';

    protected $description = 'Force regenerate all API documentation annotations, including existing ones';

    protected $annotationGenerator;

    public function __construct(ApiAnnotationGenerator $annotationGenerator)
    {
        parent::__construct();
        $this->annotationGenerator = $annotationGenerator;
    }

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $skipBackup = $this->option('skip-backup');
        $onlyMissing = $this->option('only-missing');
        $restoreBackups = $this->option('restore-backups');

        if ($restoreBackups) {
            $this->info('♻️  Restoring all last backups...');
            $this->restoreAllBackups();
            $this->info('✅ All backups restored!');
        }

        $this->info('🔄 Force Regenerating API documentation annotations...');

        if ($dryRun) {
            $this->info('📋 Dry run: No changes will be written');
        }

        $controllerPaths = config('api-doc-generator.controller_paths', [app_path('Http/Controllers/API')]);
        $totalAnnotated = 0;
        $totalSkipped = 0;
        $totalProcessed = 0;

        foreach ($controllerPaths as $path) {
            if (!File::exists($path)) {
                $this->warn("Path does not exist: {$path}");
                continue;
            }

            $controllers = File::allFiles($path);
            $this->info("\n📁 Processing controllers in: {$path}");
            $this->info("📊 Found " . count($controllers) . " controller files");

            foreach ($controllers as $controller) {
                $className = $this->getFullyQualifiedClassName($controller->getPathname());
                if (!$className || !class_exists($className)) {
                    $this->warn("Skipping: Could not load class {$className}");
                    continue;
                }

                $result = $this->processController(
                    $controller->getPathname(),
                    $className,
                    $dryRun,
                    $skipBackup,
                    $onlyMissing
                );

                $totalAnnotated += $result['annotated'];
                $totalSkipped += $result['skipped'];
                $totalProcessed += $result['processed'];
            }
        }

        $this->info("\n✅ Force regeneration completed!");
        $this->info("📊 Total methods processed: {$totalProcessed}");
        $this->info("📝 Total methods annotated: {$totalAnnotated}");
        $this->info("⏭️  Total methods skipped: {$totalSkipped}");

        if (!$dryRun) {
            $this->info("\n🎯 Now run: php artisan singhateh:generate");
            $this->info("   to generate the final API documentation");
        }
    }

    protected function restoreAllBackups(): void
    {
        $controllerPaths = config('api-doc-generator.controller_paths', [app_path('Http/Controllers/API')]);

        foreach ($controllerPaths as $path) {
            if (!File::exists($path)) {
                $this->warn("Path does not exist: {$path}");
                continue;
            }

            $controllers = File::allFiles($path);

            foreach ($controllers as $controller) {
                $backupDir = $controller->getPath() . '/__backups';
                $filename = $controller->getFilename();

                $backups = File::glob($backupDir . '/' . $filename . '.backup.*');

                if (!empty($backups)) {
                    usort($backups, fn($a, $b) => filemtime($b) <=> filemtime($a));
                    $latestBackup = $backups[0];

                    File::copy($latestBackup, $controller->getPathname());
                    $this->info("✅ Restored {$filename} from backup: " . basename($latestBackup));
                } else {
                    $this->warn("⚠️  No backup found for {$filename}");
                }
            }
        }
    }

    protected function processController(
        string $filePath,
        string $className,
        bool $dryRun = false,
        bool $skipBackup = false,
        bool $onlyMissing = false
    ): array {
        $content = File::get($filePath);
        $originalContent = $content;

        // Backup in subfolder
        if (!$dryRun && !$skipBackup) {
            $backupDir = dirname($filePath) . '/__backups';
            if (!File::exists($backupDir)) {
                File::makeDirectory($backupDir, 0755, true);
            }
            $backupPath = $backupDir . '/' . basename($filePath) . '.backup.' . date('YmdHis');
            File::put($backupPath, $content);
            $this->info("💾 Backup created: " . basename($backupPath));
        }

        $reflection = new ReflectionClass($className);
        $annotatedCount = 0;
        $skippedCount = 0;
        $processedCount = 0;

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->class !== $className) {
                continue;
            }

            $processedCount++;
            $methodName = $method->getName();
            $docComment = $method->getDocComment();

            if ($onlyMissing && $docComment && str_contains($docComment, '@api')) {
                $skippedCount++;
                continue;
            }

            // Remove only docblocks with @api, keep function intact
            if ($docComment && str_contains($docComment, '@api')) {
                $content = $this->removeExistingApiAnnotations($content, $methodName);
            }

            $route = $this->annotationGenerator->getRouteForMethod($className, $methodName);
            if (!$route) {
                $this->warn("   ⚠️  No route found for: {$className}@{$methodName}");
                $skippedCount++;
                continue;
            }

            $formRequestParams = $this->annotationGenerator->getFormRequestParameters($method);
            $routeParams = $this->annotationGenerator->getRouteParameters($route['uri']);
            $params = $this->annotationGenerator->mergeParametersWithoutDuplicates($routeParams, $formRequestParams);

            $authInfo = $this->annotationGenerator->detectAuthenticationRequirements($className, $methodName, $params);

            $annotation = $this->annotationGenerator->buildAnnotation(
                $methodName,
                $className,
                $route['uri'],
                $route['methods'][0],
                $params,
                $authInfo
            );

            if ($dryRun) {
                $this->info("   📝 Would annotate: {$className}@{$methodName}");
                $this->line('      ' . str_replace("\n", "\n      ", $annotation));
                $annotatedCount++;
            } else {
                $pattern = '/(\n\s*)(public|protected|private)\s+function\s+' . $methodName . '\s*\([^)]*\)\s*\{/';
                $content = preg_replace_callback(
                    $pattern,
                    fn($matches) => $matches[1] . $annotation . "\n" . $matches[0],
                    $content,
                    1
                );

                $annotatedCount++;
                $this->info("   ✅ Annotated: {$methodName}");
            }
        }

        if (!$dryRun && $content !== $originalContent) {
            File::put($filePath, $content);

            if (file_exists(base_path('vendor/bin/pint'))) {
                $process = proc_open(
                    base_path('vendor/bin/pint') . ' ' . escapeshellarg($filePath),
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                    $pipes
                );
                proc_close($process);
            }
        }

        return [
            'annotated' => $annotatedCount,
            'skipped' => $skippedCount,
            'processed' => $processedCount
        ];
    }

    protected function removeExistingApiAnnotations(string $content, string $methodName): string
    {
        // Only remove docblock above the method
        $pattern = '/\/\*\*[\s\S]*?\*\/(\s*)(public|protected|private)\s+function\s+' . preg_quote($methodName, '/') . '\s*\(/';
        return preg_replace_callback($pattern, fn($matches) => $matches[1] . $matches[2] . ' function ' . $methodName . '(', $content);
    }

    protected function getFullyQualifiedClassName(string $filePath): ?string
    {
        $content = File::get($filePath);
        if (!preg_match('/namespace\s+(.+?);/', $content, $nsMatch)) {
            return null;
        }
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }

        return $nsMatch[1] . '\\' . $classMatch[1];
    }
}
