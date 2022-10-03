<?php

namespace Cactuar\Admin\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
		'root'	=> \Cactuar\Admin\Http\Middleware\Root::class,
		'admin' => \Cactuar\Admin\Http\Middleware\Admin::class,
        'minify' => \Cactuar\Admin\Http\Middleware\Minify::class,
		'front' => \App\Http\Middleware\Front::class,
    ];
}
