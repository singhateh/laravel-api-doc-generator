@extends('api-doc-generator::layouts.error')

@section('content')
<div class="min-h-screen dark:bg-gradient-to-br dark:from-gray-900 dark:to-gray-800 flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full space-y-8 text-center">

        <!-- Animated Error Code -->
        <div class="relative mb-6">
            <div class="text-9xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 animate-pulse">
                {{ $code }}
            </div>
            <div class="absolute inset-0 text-9xl font-bold text-purple-400/20 blur-sm">
                {{ $code }}
            </div>
        </div>

        <!-- Title -->
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100 mb-4 tracking-wide transition-colors duration-300">
            <i class="{{ $icon ?? 'fas fa-exclamation-triangle' }} text-purple-400 mr-2"></i>
            {{ $title }}
        </h1>

        <!-- Message -->
        <p class="text-gray-600 dark:text-gray-300 mb-8 leading-relaxed text-lg transition-colors duration-300">
            {{ $message }}
        </p>

        <!-- Action buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('api-docs.index') }}" 
               class="group relative flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg font-semibold shadow-lg hover:shadow-purple-500/25 transition-all duration-300 hover:scale-105">
                <i class="fas fa-home mr-2 group-hover:animate-bounce transition-transform duration-300"></i>
                <span>Go Home</span>
            </a>
        </div>

    </div>
</div>
@endsection
