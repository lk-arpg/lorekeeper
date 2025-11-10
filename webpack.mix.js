const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Scripts
mix.js('resources/js/app.js', 'public/js')
    .version();
mix.js('resources/js/app-secondary.js', 'public/js')
    .version();

// CSS
mix.sass('resources/sass/app.scss', 'public/css')
    .version();
