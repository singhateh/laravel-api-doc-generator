<?php

namespace Alagiesinghateh\LaravelApiDocGenerator;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use Carbon\Carbon;

class ApiDocGenerator
{
    protected $files;

    public function __construct($files = [])
    {
        $this->files = $files;
    }

    /**
     * Generate API documentation from controller annotations with backup
     */
    public function generate()
    {
        $docs = [];

        // Only scan the API controllers path from config
        $controllerPaths = array_filter(
            config('api-doc-generator.controller_paths', [app_path('Http/Controllers/API')]),
            fn ($path) => str_contains($path, 'API') && File::exists($path)
        );

        foreach ($controllerPaths as $controllerPath) {
            $controllers = File::allFiles($controllerPath);

            foreach ($controllers as $controller) {
                $className = $this->getClassNameFromPath($controllerPath, $controller);

                if (! $className || ! class_exists($className)) {
                    continue;
                }

                try {
                    $reflection = new ReflectionClass($className);
                    $docs = array_merge($docs, $this->processControllerMethods($reflection));
                } catch (\ReflectionException $e) {
                    continue;
                }
            }
        }

        $result = $this->generateJsonFile($docs);

        return [
            'docs' => $docs,
            'backup_created' => $result['backup_created'],
            'file_updated' => $result['file_updated']
        ];
    }

    /**
     * Get fully qualified class name from file
     */
    protected function getClassNameFromPath($basePath, $file)
    {
        $relativePath = str_replace([$basePath, '.php'], '', $file->getRealPath());
        $className = str_replace('/', '\\', trim($relativePath, '/'));

        $appNamespace = app()->getNamespace();

        return $appNamespace.'Http\\Controllers\\API\\'.$className; // Only API namespace
    }

    /**
     * Process all public methods of a controller and extract @api docblocks
     */
    protected function processControllerMethods(ReflectionClass $reflection)
    {
        $docs = [];

        foreach ($reflection->getMethods() as $method) {
            $docComment = $method->getDocComment();

            if ($docComment && str_contains($docComment, '@api')) {
                $parsed = $this->parseDocBlock($docComment);
                if (! empty($parsed)) {
                    $docs[] = $parsed;
                }
            }
        }

        return $docs;
    }

    /**
     * Parse a single @api docblock into structured data
     */
   protected function parseDocBlock(string $docComment): array
{
    $defaults = config('api-doc-generator.defaults');

    $data = [
        'id' => Str::random(10),
        'method' => $defaults['method'],
        'path' => $defaults['path'],
        'name' => $defaults['name'],
        'group' => $defaults['group'],
        'description' => '',
        'authenticated' => false,
        'middleware' => [], // Add middleware field
        'parameters' => [],
        'responses' => [
            'examples' => [
                'success' => '',
                'error' => '',
            ],
        ],
        'headers' => [],
        'errors' => [],
    ];

    $currentTag = null;
    $lines = explode("\n", $docComment);

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || $line === '/**' || $line === '*/') {
            continue;
        }

        $line = preg_replace('/^\s*\*\s?/', '', $line);

        if (preg_match('/@api\s+\{(\w+)\}\s+(\S+)\s+(.+)/', $line, $matches)) {
            $data['method'] = strtoupper($matches[1]);
            $data['path'] = $matches[2];
            $data['description'] = $matches[3];
        } elseif (preg_match('/@apiName\s+(.+)/', $line, $matches)) {
            $data['name'] = trim($matches[1]);
        } elseif (preg_match('/@apiGroup\s+(.+)/', $line, $matches)) {
            $data['group'] = trim($matches[1]);
        } elseif (preg_match('/@apiDescription\s+(.+)/', $line, $matches)) {
            $data['description'] = trim($matches[1]);
        } elseif (preg_match('/@apiParam\s+\{(.*?)\}\s+(\[?)(.*?)(\]?)\s+(.+)/', $line, $matches)) {
            $data['parameters'][] = [
                'type' => $matches[1],
                'name' => $matches[3],
                'required' => empty($matches[2]) && empty($matches[4]),
                'description' => $matches[5],
            ];
        } elseif (preg_match('/@apiHeader\s+\{(.*?)\}\s+(.+)/', $line, $matches)) {
            $data['headers'][] = [
                'name' => $matches[2],
                'type' => $matches[1],
            ];
        } elseif (preg_match('/@apiSuccess\s+\{(.*?)\}\s+(.+)/', $line, $matches)) {
            $data['responses']['fields'][] = [
                'type' => $matches[1],
                'field' => $matches[2],
            ];
        } elseif (preg_match('/@apiSuccessExample/', $line)) {
            $currentTag = 'success';
            $data['responses']['examples'][$currentTag] = '';
        } elseif (preg_match('/@apiErrorExample/', $line)) {
            $currentTag = 'error';
            $data['responses']['examples'][$currentTag] = '';
        } elseif (preg_match('/@apiError\s+\{(.*?)\}\s+(.+)/', $line, $matches)) {
            $data['errors'][] = [
                'code' => $matches[1],
                'description' => $matches[2],
            ];
        } elseif (preg_match('/@apiAuth\s+(.+)/', $line, $matches)) {
            // Handle authentication annotation
            $data['authenticated'] = true;
            $data['auth_type'] = trim($matches[1]);
        } elseif (preg_match('/@apiPermission\s+(.+)/', $line, $matches)) {
            // Handle permission annotation
            $data['authenticated'] = true;
            $data['permissions'] = array_map('trim', explode(',', $matches[1]));
        } elseif (preg_match('/@apiSecurity\s+(.+)/', $line, $matches)) {
            // Handle security annotation
            $data['authenticated'] = true;
            $data['security_scheme'] = trim($matches[1]);
        } elseif (preg_match('/@apiMiddleware\s+(.+)/', $line, $matches)) {
            $data['middleware'][] = trim($matches[1]);
        } elseif ($currentTag && ! str_starts_with($line, '@')) {
            $data['responses']['examples'][$currentTag] .= $line."\n";
        }
    }

    // Additional authentication detection from parameters
    if (!$data['authenticated']) {
        $data['authenticated'] = $this->detectAuthenticationFromParameters($data['parameters']);
    }

    // Additional authentication detection from headers
    if (!$data['authenticated']) {
        $data['authenticated'] = $this->detectAuthenticationFromHeaders($data['headers']);
    }

    $data['responses']['examples']['success'] = trim($data['responses']['examples']['success']);
    $data['responses']['examples']['error'] = trim($data['responses']['examples']['error']);

    if ($data['name'] === 'Untitled Endpoint' && ! empty($data['description'])) {
        $data['name'] = $data['description'];
    }

    return $data;
}

    /**
     * Detect authentication requirements from parameters
     */
    protected function detectAuthenticationFromParameters(array $parameters): bool
    {
        $authIndicators = [
            'token', 'api_key', 'api-key', 'bearer', 'jwt', 'auth', 
            'authorization', 'access_token', 'access-token'
        ];

        foreach ($parameters as $param) {
            $paramName = strtolower($param['name']);
            if (in_array($paramName, $authIndicators)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Detect authentication requirements from headers
     */
    protected function detectAuthenticationFromHeaders(array $headers): bool
    {
        $authIndicators = [
            'authorization', 'x-api-key', 'x-api-token', 'x-auth-token',
            'x-access-token', 'x-bearer-token'
        ];

        foreach ($headers as $header) {
            $headerName = strtolower($header['name']);
            if (in_array($headerName, $authIndicators)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate JSON file for documentation with backup only if content changes
     */
    protected function generateJsonFile(array $docs): array
    {
        $outputDir = config('api-doc-generator.output_dir');

        if (! File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $outputFile = "{$outputDir}/api-docs.json";
        
        $grouped = [];
        foreach ($docs as $doc) {
            $group = $doc['group'] ?? 'General';
            $grouped[$group]['endpoints'][] = $doc;
        }

        $newContent = json_encode($grouped, JSON_PRETTY_PRINT);
        
        $backupCreated = false;
        $fileUpdated = false;

        // Check if file exists and compare content
        if (File::exists($outputFile)) {
            $currentContent = File::get($outputFile);
            
            // Normalize JSON for comparison (remove whitespace differences)
            $normalizedCurrent = $this->normalizeJson($currentContent);
            $normalizedNew = $this->normalizeJson($newContent);
            
            if ($normalizedCurrent !== $normalizedNew) {
                // Content is different, create backup and update file
                $this->createBackup($outputFile, $currentContent);
                File::put($outputFile, $newContent);
                $backupCreated = true;
                $fileUpdated = true;
            } else {
                // Content is the same, no need to update
                $fileUpdated = false;
            }
        } else {
            // File doesn't exist, just create it (no backup needed)
            File::put($outputFile, $newContent);
            $fileUpdated = true;
        }

        // Clean up old backups
        $this->cleanupOldBackups($outputDir);

        return [
            'backup_created' => $backupCreated,
            'file_updated' => $fileUpdated
        ];
    }

    /**
     * Normalize JSON for comparison (remove whitespace and formatting differences)
     */
    protected function normalizeJson(string $json): string
    {
        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (\JsonException $e) {
            return $json; // Return original if invalid JSON
        }
    }

    /**
     * Create backup of existing documentation file
     */
    protected function createBackup(string $filePath, string $content = null): void
    {
        $backupDir = dirname($filePath) . '/backups';
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $timestamp = Carbon::now()->format('Y-m-d_His');
        $filename = pathinfo($filePath, PATHINFO_FILENAME);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        $backupPath = "{$backupDir}/{$filename}_{$timestamp}.{$extension}";

        if ($content !== null) {
            // Use provided content
            File::put($backupPath, $content);
        } else {
            // Copy file
            File::copy($filePath, $backupPath);
        }
    }

    /**
     * Clean up old backups, keeping only the last N backups
     */
    protected function cleanupOldBackups(string $outputDir): void
    {
        $backupDir = $outputDir . '/backups';
        if (!File::exists($backupDir)) {
            return;
        }

        $maxBackups = config('api-doc-generator.max_backups', 10);
        $backupFiles = File::files($backupDir);

        // Sort by modified time (newest first)
        usort($backupFiles, function ($a, $b) {
            return File::lastModified($b) - File::lastModified($a);
        });

        // Remove old backups beyond the limit
        foreach (array_slice($backupFiles, $maxBackups) as $oldBackup) {
            File::delete($oldBackup->getPathname());
        }
    }

    /**
     * Restore from backup
     */
    public function restoreFromBackup(string $backupFilename = null): array
    {
        $outputDir = config('api-doc-generator.output_dir');
        $backupDir = $outputDir . '/backups';
        $outputFile = "{$outputDir}/api-docs.json";

        if (!File::exists($backupDir)) {
            return ['success' => false, 'message' => 'Backup directory does not exist'];
        }

        if ($backupFilename) {
            $backupPath = "{$backupDir}/{$backupFilename}";
            if (!File::exists($backupPath)) {
                return ['success' => false, 'message' => 'Backup file not found'];
            }
        } else {
            // Get the latest backup
            $backupFiles = File::files($backupDir);
            if (empty($backupFiles)) {
                return ['success' => false, 'message' => 'No backups available'];
            }

            // Sort by modified time (newest first)
            usort($backupFiles, function ($a, $b) {
                return File::lastModified($b) - File::lastModified($a);
            });

            $backupPath = $backupFiles[0]->getPathname();
            $backupFilename = $backupFiles[0]->getFilename();
        }

        // Create backup of current file before restoring (if it exists)
        if (File::exists($outputFile)) {
            $currentContent = File::get($outputFile);
            $this->createBackup($outputFile, $currentContent);
        }

        // Restore from backup
        File::copy($backupPath, $outputFile);

        return [
            'success' => true, 
            'message' => 'Backup restored successfully',
            'backup_file' => $backupFilename
        ];
    }

    /**
     * List available backups
     */
    public function listBackups(): array
    {
        $outputDir = config('api-doc-generator.output_dir');
        $backupDir = $outputDir . '/backups';

        if (!File::exists($backupDir)) {
            return [];
        }

        $backups = [];
        $backupFiles = File::files($backupDir);

        foreach ($backupFiles as $file) {
            $backups[] = [
                'filename' => $file->getFilename(),
                'size' => File::size($file->getPathname()),
                'modified' => Carbon::createFromTimestamp(File::lastModified($file->getPathname())),
                'path' => $file->getPathname()
            ];
        }

        // Sort by modified time (newest first)
        usort($backups, function ($a, $b) {
            return $b['modified']->timestamp - $a['modified']->timestamp;
        });

        return $backups;
    }

    /**
     * Compare current documentation with new content to see if backup is needed
     */
    public function needsBackup(array $newDocs): bool
    {
        $outputFile = config('api-doc-generator.output_dir') . '/api-docs.json';
        
        if (!File::exists($outputFile)) {
            return false; // No existing file, no backup needed
        }

        $currentContent = File::get($outputFile);
        
        $grouped = [];
        foreach ($newDocs as $doc) {
            $group = $doc['group'] ?? 'General';
            $grouped[$group]['endpoints'][] = $doc;
        }

        $newContent = json_encode($grouped, JSON_PRETTY_PRINT);
        
        // Normalize JSON for comparison
        $normalizedCurrent = $this->normalizeJson($currentContent);
        $normalizedNew = $this->normalizeJson($newContent);
        
        return $normalizedCurrent !== $normalizedNew;
    }

    /**
     * Get existing documentation or regenerate if missing
     */
    public function getDocumentation()
    {
        $docsPath = config('api-doc-generator.output_dir').'/api-docs.json';

        if (! File::exists($docsPath)) {
            $result = $this->generate();
            return $result['docs'];
        }

        $docs = json_decode(File::get($docsPath), true);

        // Ensure all endpoints have authenticated field
        foreach ($docs as $groupName => $groupData) {
            foreach ($groupData['endpoints'] as $index => $endpoint) {
                if (!isset($endpoint['authenticated'])) {
                    $docs[$groupName]['endpoints'][$index]['authenticated'] = false;
                }
            }
        }

        return $docs;
    }
}