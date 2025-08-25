<?php

// tests/Unit/ClassLoadingTest.php

test('debug class loading', function () {
    // Create a simple test class
    $testPath = storage_path('test-classes');
    if (! File::exists($testPath)) {
        File::makeDirectory($testPath, 0755, true);
    }

    $classContent = <<<'PHP'
<?php

namespace Test\Namespace;

class TestClass
{
    public function testMethod()
    {
        return 'test';
    }
}
PHP;

    File::put($testPath.'/TestClass.php', $classContent);

    // Add to autoloader
    $loader = require __DIR__.'/../../vendor/autoload.php';
    $loader->addPsr4('Test\\Namespace\\', $testPath);

    // Check if class can be loaded
    $className = 'Test\Namespace\TestClass';
    $classExists = class_exists($className);

    echo 'Class exists: '.($classExists ? 'YES' : 'NO')."\n";

    if ($classExists) {
        $instance = new $className;
        echo 'Method works: '.$instance->testMethod()."\n";
    } else {
        echo "Trying to manually include the file...\n";
        include $testPath.'/TestClass.php';

        $classExistsAfterInclude = class_exists($className);
        echo 'Class exists after include: '.($classExistsAfterInclude ? 'YES' : 'NO')."\n";
    }

    // Clean up
    File::deleteDirectory($testPath);
});
