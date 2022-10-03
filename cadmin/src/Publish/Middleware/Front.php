<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

use Cactuar\Admin\Helpers\lang;
use Cactuar\Admin\Helpers\helper;
use Cactuar\Admin\Models\Conf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

class Front
{
    public function handle($request, Closure $next, $guard = null)
    {
        lang::initial();
        
		$webConfig = Conf::initial('site-setting');
        if (!$webConfig) {
            echo view('errors.offline');
            die();
        }
        
        /*if (App::environment('production')) { //disabled as request, recommend redirect by htaccess / cpanel
            if (starts_with($request->header('host'), 'www.') && !$webConfig->value('use-www')) {
                $host = str_replace('www.', '', $request->header('host'));
                $request->headers->set('host', $host);
    
                return Redirect::to($request->fullUrl(), 301);
            } elseif (!starts_with($request->header('host'), 'www.') && $webConfig->value('use-www')) {
                $host = 'www.' . $request->header('host');
                $request->headers->set('host', $host);
    
                return Redirect::to($request->fullUrl(), 301);
            }
        }*/
        
        view()->share('webConfig', $webConfig);
		$request->attributes->add(['webConfig' => $webConfig]);
		
        if ($webConfig->status == 'offline') {
            echo view('errors.offline');
            die();
        }
        
        return $next($request);
    }
}
