<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Http\Controllers;

use Alagiesinghateh\LaravelApiDocGenerator\ApiDocGenerator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class ApiDocumentationController extends Controller
{
    protected $generator;

    public function __construct(ApiDocGenerator $generator)
    {
        $this->generator = $generator;
    }

    public function index()
    {
        $docs = $this->generator->getDocumentation();


        if (empty($docs)) {
            return response()->view('api-doc-generator::error', [
                'message' => 'No API documentation found. Please run php artisan singhateh:generate first.',
            ], 404);
        }

        return view('api-doc-generator::index', compact('docs'));
    }

    public function show($group = null)
    {
        $docs = $this->generator->getDocumentation();

        if ($group && ! isset($docs[$group])) {
            return response()->view('api-doc-generator::error', [
                'message' => "API group '{$group}' not found.",
            ], 404);
        }

        if (! $group && ! empty($docs)) {
            $group = array_key_first($docs);

            return redirect()->route('api-docs.group', ['group' => $group]);
        }

        return view('api-doc-generator::group', [
            'group' => $group,
            'endpoints' => $docs[$group]['endpoints'],
            'allGroups' => array_keys($docs),
            'docs' => $docs,
        ]);
    }

    public function showEndpoint($id)
    {
        $docs = $this->generator->getDocumentation();

        $endpoint = null;
        $groupName = null;

        foreach ($docs as $group => $data) {
            foreach ($data['endpoints'] as $ep) {
                if ($ep['id'] === $id) {
                    $endpoint = $ep;
                    $groupName = $group;
                    break 2;
                }
            }
        }

        if (! $endpoint) {
            return response()->view('api-doc-generator::error', [
                'message' => "Endpoint with ID '{$id}' not found.",
            ], 404);
        }

        return view('api-doc-generator::endpoint', [
            'endpoint' => $endpoint,
            'group' => $groupName,
            'allGroups' => array_keys($docs),
            'docs' => array_keys($docs),
        ]);
    }

    public function json()
    {
        $docs = $this->generator->getDocumentation();

        if (empty($docs)) {
            return response()->json([
                'error' => 'API documentation not found. Please run php artisan singhateh:generate first.',
            ], 404);
        }

        return response()->json($docs);
    }

    public function testEndpoint(Request $request)
    {
        if (! config('api-doc-generator.testing.enabled')) {
            return response()->json(['error' => 'API testing is disabled'], 403);
        }

        $validated = $request->validate([
            'url' => 'required|url',
            'method' => 'required|in:GET,POST,PUT,PATCH,DELETE,OPTIONS,HEAD',
            'headers' => 'array',
            'body' => 'array',
            'query_params' => 'array',
            'auth_type' => 'nullable|in:bearer,basic,api_key',
            'auth_token' => 'nullable|string',
            'auth_username' => 'nullable|string',
            'auth_password' => 'nullable|string',
            'auth_key' => 'nullable|string',
            'auth_value' => 'nullable|string',
        ]);

        try {
            $client = Http::timeout(config('api-doc-generator.testing.timeout', 30))
                ->withHeaders($validated['headers'] ?? []);

            // Handle authentication
            if (! empty($validated['auth_type'])) {
                $client = $this->applyAuth($client, $validated);
            }

            // Handle request
            $method = strtolower($validated['method']);
            $url = $validated['url'];

            // Add query parameters for GET requests
            if ($method === 'get' && ! empty($validated['query_params'])) {
                $url .= (strpos($url, '?') === false ? '?' : '&').http_build_query($validated['query_params']);
            }

            $response = $client->{$method}(
                $url,
                in_array($method, ['post', 'put', 'patch']) ? $validated['body'] ?? [] : []
            );

            return response()->json([
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->json() ?? $response->body(),
                'size' => strlen($response->body()),
                'time' => $response->transferStats?->getTransferTime() ?? 0,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'type' => get_class($e),
            ], 500);
        }
    }

    protected function applyAuth($client, $validated)
    {
        switch ($validated['auth_type']) {
            case 'bearer':
                return $client->withToken($validated['auth_token'] ?? '');
            case 'basic':
                return $client->withBasicAuth(
                    $validated['auth_username'] ?? '',
                    $validated['auth_password'] ?? ''
                );
            case 'api_key':
                return $client->withHeaders([
                    $validated['auth_key'] ?? 'Authorization' => $validated['auth_value'] ?? '',
                ]);
            default:
                return $client;
        }
    }

    public function endpoint($id)
    {
        $docs = $this->generator->getDocumentation();

        $endpoint = null;
        $groupName = null;

        foreach ($docs as $group => $data) {
            foreach ($data['endpoints'] as $ep) {
                if ($ep['id'] === $id) {
                    $endpoint = $ep;
                    $groupName = $group;
                    break 2;
                }
            }
        }

        if (! $endpoint) {
            return response()->view('api-doc-generator::error', [
                'message' => "Endpoint with ID '{$id}' not found.",
            ], 404);
        }

        return view('api-doc-generator::endpoint', [
            'endpoint' => $endpoint,
            'group' => $groupName,
            'allGroups' => array_keys($docs),
            'docs' => $docs,
        ]);
    }
}
