<?php

if (!admin::availByDomain())
    return false;

if (!admin::availByIp())
    return false;

define('ADMIN', config('cadmin.cadmin.admin-url'));
$prefered = config('cadmin.prefered-routes');

Route::group(['middleware' => ['web', 'root']], function() use($prefered) {
    Route::get(ADMIN.'/', array_get($prefered, 'getLogin'));
    Route::get(ADMIN.'/guest/login', array_get($prefered, 'getLogin'));
    Route::post(ADMIN.'/guest/login', array_get($prefered, 'postLogin'));
    Route::get(ADMIN.'/logout', array_get($prefered, 'getLogout'));
    Route::get(ADMIN.'/guest/forgot', array_get($prefered, 'getForgot'));
    Route::post(ADMIN.'/guest/forgot', array_get($prefered, 'postForgot'));
    Route::get(ADMIN.'/guest/reset-password', array_get($prefered, 'getResetPassword'));
    
    Route::group(['middleware' => ['admin']], function() use ($prefered) {
        Route::get(ADMIN.'/profile', array_get($prefered, 'getProfile'));
        Route::post(ADMIN.'/profile', array_get($prefered, 'postProfile'));
        Route::get(ADMIN.'/profile/chpass', array_get($prefered, 'getProfileChpass'));
        Route::post(ADMIN.'/profile/chpass', array_get($prefered, 'postProfileChpass'));
        Route::post(ADMIN.'/validate/unique', array_get($prefered, 'postValidateUnique'));
        Route::post(ADMIN.'/admin/filter', array_get($prefered, 'postAdminFilter'));
		//Route::post(ADMIN.'/section-editor/gallery', array_get($prefered, 'postSectionEditorGallery'));
		//Route::post(ADMIN.'/section-editor/editor', array_get($prefered, 'postSectionEditorForm'));
        
        Route::get(ADMIN.'/documentation',function() {
            if (!config('cadmin.cadmin.documentation-file'))
                abort(404);
            if (!file_exists(config('cadmin.cadmin.documentation-file')))
                abort(404);
        
            return response()->download(config('cadmin.cadmin.documentation-file'));
        });
    });
    
    $routing = function($key, $module)
    {
        if (!array_get($module, 'controller'))
            return;
        
        if (is_array(array_get($module, 'routes.get'))) {
            foreach ($module['routes']['get'] as $k => $v) {
                if ($v == 'index')
                    Route::get(ADMIN.'/'.$key, array_get($module, 'controller').'@'.helper::dash2camel('get-'.$v));   
                Route::get(ADMIN.'/'.$key.'/'.$v, array_get($module, 'controller').'@'.helper::dash2camel('get-'.$v)); 
            }
        }
        if (is_array(array_get($module, 'routes.post'))) {
            foreach ($module['routes']['post'] as $k => $v) {
                if ($v == 'index')
                    Route::post(ADMIN.'/'.$key, array_get($module, 'controller').'@'.helper::dash2camel('get-'.$v));    
                Route::post(ADMIN.'/'.$key.'/'.$v, array_get($module, 'controller').'@'.helper::dash2camel('post-'.$v)); 
            }
        }
    };
    
    if (is_array(config('cadmin.menu'))) {
        Route::group(['middleware' => ['admin:permission']], function() use($routing) {
            foreach (config('cadmin.menu') as $k => $v) {
                if (is_array(array_get($v, 'routes.post')) || is_array(array_get($v, 'routes.get'))) {
                    $routing($k, $v);
                }
            }
        });
    }
    
    if (is_array(config('cadmin.menu-root'))) {
        Route::group(['middleware' => ['admin:root']], function() use ($routing) {
            foreach (config('cadmin.menu-root') as $k => $v) {
                if (is_array(array_get($v, 'routes.post')) || is_array(array_get($v, 'routes.get'))) {
                    $routing($k, $v);
                }
            }
        });
    }
});

$thumbPath = config('cadmin.media.thumbpath');

if ($thumbPath) {
    Route::group(['middleware' => ['web']], function() use ($prefered,$thumbPath) {
        $sections = [];
        for($i=1;$i<=count(request()->segments());$i++)
            $sections[] = '{section'.$i.'?}';
        Route::get($thumbPath.'/'.implode('/',$sections), array_get($prefered, 'getThumb'));
    });
}

Route::get(config('cadmin.minify.public-path.css').'/minify-{code}.min.css',function($code) {
    return minify('css')->build($code); 
});
Route::get(config('cadmin.minify.public-path.js').'/minify-{code}.min.js',function($code) {
    return minify('js')->build($code); 
});