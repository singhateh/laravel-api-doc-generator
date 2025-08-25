<?php

namespace Alagiesinghateh\LaravelApiDocGenerator\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ProtectApiDocs
{
    public function handle(Request $request, Closure $next): Response
    {
        $config = config('api-doc-generator');
        $routePrefix = trim($config['web_interface']['route_prefix'] ?? 'api-docs', '/');

        $docsPath = config('api-doc-generator.output_dir').'/api-docs.json';

        if (! File::exists($docsPath)) {
            return $this->handleErrorResponse(
                404,
                'Documentation Not Found',
                'API documentation has not been generated yet. Please run: php artisan singhateh:generate',
                $request
            );
        }

        // Helper to handle error responses
        $errorResponse = function ($code, $title, $message) use ($request) {
            return $this->handleErrorResponse($code, $title, $message, $request);
        };

        /**
         * 🚫 Handle production environment
         */
        if (app()->environment('production')) {
            $allowedUsers = $config['allowed_users'] ?? [];
            $ipWhitelist = $config['security']['ip_whitelist'] ?? [];

            $userAllowed = auth()->check() && in_array(auth()->user()->email, $allowedUsers, true);
            $ipAllowed = ! empty($ipWhitelist) && in_array($request->ip(), $ipWhitelist, true);

            if (! ($userAllowed || $ipAllowed)) {
                return $errorResponse(
                    403,
                    'Access Denied',
                    'API documentation is disabled in production for your account or IP.'
                );
            }
        } else {
            /**
             * 👤 Development / staging environments
             * Always allow localhost unless overridden.
             */
            $allowedUsers = $config['allowed_users'] ?? [];
            $ipWhitelist = $config['security']['ip_whitelist'] ?? ['127.0.0.1', '::1'];

            $userAllowed = auth()->check() && in_array(auth()->user()->email, $allowedUsers, true);
            $ipAllowed = empty($ipWhitelist) || in_array($request->ip(), $ipWhitelist, true);

            if (! ($userAllowed || $ipAllowed)) {
                return $errorResponse(
                    403,
                    'Access Denied',
                    'You are not authorized to access API documentation.'
                );
            }
        }

        /**
         * 🔒 Restrict specific paths (like /api-docs/internal/*)
         */
        foreach ($config['restricted_paths'] ?? [] as $path) {
            $path = trim($path, '/');
            if ($request->is("{$routePrefix}/{$path}*")) {
                return $errorResponse(
                    403,
                    'Restricted',
                    'This section of the API documentation is restricted.'
                );
            }
        }

        return $next($request);
    }

    /**
     * Handle error responses with proper view checking
     */
    protected function handleErrorResponse(int $code, string $title, string $message, Request $request): Response
    {
        $packageErrorView = 'api-doc-generator::errors.base';

        // Check if the package error view exists
        if (View::exists($packageErrorView)) {
            return response()->view($packageErrorView, [
                'code' => $code,
                'title' => $title,
                'message' => $message,
                'icon' => $this->getErrorIcon($code),
            ], $code);
        }

        // Fallback to Laravel's error handling
        return $this->fallbackErrorResponse($code, $title, $message, $request);
    }

    /**
     * Get appropriate icon for error code
     */
    protected function getErrorIcon(int $code): string
    {
        return match ($code) {
            403 => 'fas fa-lock',
            404 => 'fas fa-map-marker-alt',
            500 => 'fas fa-bug',
            default => 'fas fa-exclamation-triangle',
        };
    }

    /**
     * Fallback error response when package view doesn't exist
     */
    protected function fallbackErrorResponse(int $code, string $title, string $message, Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => [
                    'code' => $code,
                    'message' => $message,
                    'title' => $title,
                ],
            ], $code);
        }

        // Use Laravel's built-in error handling
        abort($code, "{$title}: {$message}");
    }
}
