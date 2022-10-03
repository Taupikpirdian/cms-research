<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use App\Models\Page;

Route::group(['middlewareGroups' => ['web']], function () {
    Route::group(['middleware' => ['root','front']], function () {
        
    $routes = [ //routes parameter, get or post
        'get' => [
            ''              => 'HomeController@getIndex',
            'home'          => 'HomeController@getIndex',
        ],
        'post' => []
    ];
    
    $routeType = [ //route by page type
        //'career' => 'CareerController@getIndex', //example
    ];
    
    foreach (Page::raw() AS $v) {
        if ($v['type'] == 'void' || $v['type'] == 'link')
            continue;

        if (array_key_exists($v['type'], $routeType)) 
            $routes['get'][$v['target']] = $routeType[$v['type']];
        else 
            $routes['get'][$v['target']] = 'PageController@getIndex';
    }
    
    lang::buildRoutes($routes);  
            
    });
});
                     
//});
