const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(mix => {
    
mix.scripts([
	'script.js'
],'public/js/script.js');

mix.less([
	'style.less'
],'public/css/style.css');

mix.styles([
	'w3.css'
],'public/css/w3.css' );





});
