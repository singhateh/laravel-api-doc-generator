const mix = require('laravel-mix');

// Build API Docs CSS
mix.postCss('resources/css/api-docs.css', 'public/css', [
    require('tailwindcss'),
    require('autoprefixer'),
]);

// If you have JS files
mix.js('resources/js/api-docs.js', 'public/js');

// Copy Lucide icons for offline use
mix.copy('node_modules/lucide/dist/umd/lucide.js', 'public/vendor/lucide/lucide.js');
mix.copy('node_modules/lucide/dist/umd/lucide.js.map', 'public/vendor/lucide/lucide.js.map');

// Copy Highlight.js for offline use
mix.copy('node_modules/highlight.js/lib/common/highlight.js', 'public/vendor/highlight.js/highlight.min.js');
mix.copy('node_modules/highlight.js/styles/github-dark.min.css', 'public/vendor/highlight.js/github-dark.min.css');

// Copy Axios for offline use
mix.copy('node_modules/axios/dist/axios.min.js', 'public/vendor/axios/axios.min.js');
mix.copy('node_modules/axios/dist/axios.min.js.map', 'public/vendor/axios/axios.min.js.map');