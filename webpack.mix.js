const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss'); /* Add this line at the top */

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// mix.js('resources/assets/js/app.js', 'public/assets/js').postCss('resources/assets/css/app.css', 'public/assets/css', [
//     require('tailwindcss'),
//     require('autoprefixer'),
// ]);

mix
    // js
    .js('resources/assets/js/app.js', 'public/assets/js')
    .js('resources/assets/js/head.js', 'public/assets/js')
    .js('resources/assets/js/footer.js', 'public/assets/js')
    // vue
    .js('resources/assets/vue/vue-dashboard-list.js', 'public/vue/vue-dashboard-list.js').vue()
    .js('resources/assets/vue/vue-equipment-list.js', 'public/vue/vue-equipment-list.js').vue()
    .js('resources/assets/vue/vue-equipment-site.js', 'public/vue/vue-equipment-site.js').vue()
    .js('resources/assets/vue/vue-dashboard-filters.js', 'public/vue/vue-dashboard-filters.js').vue()
    .js('resources/assets/vue/vue-equipment-filters.js', 'public/vue/vue-equipment-filters.js').vue()
    .js('resources/assets/vue/vue-loading-indicator.js', 'public/vue/vue-loading-indicator.js').vue()
    .js('resources/assets/vue/vue-charts.js', 'public/vue/vue-charts.js').vue()
    .sass('resources/assets/sass/app-new.scss', 'public/assets/css')
    // .sass('resources/assets/sass/app.scss', 'public/assets/css')
    // .copy('resources/assets/images', 'public/assets/images')
    // .copy('resources/assets/themes', 'public/assets/themes')
    // .copy('resources/assets/fonts', 'public/assets/fonts')
    // .options({
    //     postCss: [ tailwindcss('./tailwind.config.js') ],
    // })
    .version();
