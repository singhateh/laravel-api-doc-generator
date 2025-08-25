<?php

// tests/Feature/FileScanningTest.php

use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGenerator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

test('debug file scanning', function () {
    $generator = new ApiDocGenerator(app('files'));

    // Create test controller in a simple location
    $testPath = storage_path('test-scan');
    if (! File::exists($testPath)) {
        File::makeDirectory($testPath, 0755, true);
    }

    $controllerContent = <<<'PHP'
<?php

class SimpleTestController
{
    /**
     * @api {get} /api/simple Get simple data
     * @apiName GetSimple
     * @apiGroup Simple
     */
    public function index()
    {
        return 'simple';
    }
}
PHP;

    File::put($testPath.'/SimpleTestController.php', $controllerContent);

    // Set config to use test path
    Config::set('api-doc-generator.controller_paths', [$testPath]);
    Config::set('api-doc-generator.output_dir', storage_path('test-api-docs'));

    // Debug: Check what paths are being scanned
    $paths = Config::get('api-doc-generator.controller_paths');
    echo 'Scanning paths: '.implode(', ', $paths)."\n";

    foreach ($paths as $path) {
        if (! File::exists($path)) {
            echo "Path does not exist: $path\n";

            continue;
        }

        $files = File::allFiles($path);
        echo 'Found '.count($files)." files in $path:\n";

        foreach ($files as $file) {
            echo '  - '.$file->getRelativePathname()."\n";

            // Test the getClassNameFromPath method
            $reflection = new ReflectionClass($generator);
            $method = $reflection->getMethod('getClassNameFromPath');
            $method->setAccessible(true);

            try {
                $className = $method->invoke($generator, $path, $file);
                echo "    Class name: $className\n";

                // Check if class exists
                if (class_exists($className)) {
                    echo "    ✓ Class exists\n";
                } else {
                    echo "    ✗ Class does not exist\n";

                    // Try to include manually
                    include $file->getPathname();
                    if (class_exists($className)) {
                        echo "    ✓ Class exists after manual include\n";
                    } else {
                        echo "    ✗ Class still does not exist after include\n";
                    }
                }
            } catch (\Exception $e) {
                echo '    Error getting class name: '.$e->getMessage()."\n";
            }
        }
    }

    // Try to generate documentation
    $docs = $generator->generate();
    echo 'Generated docs: '.json_encode($docs, JSON_PRETTY_PRINT)."\n";

    // Clean up
    File::deleteDirectory($testPath);
    File::deleteDirectory(storage_path('test-api-docs'));
});
