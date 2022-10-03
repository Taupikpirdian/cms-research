<?php namespace Cactuar\Admin\Http\Middleware;

use Closure;
use Validator;
use Cactuar\Admin\Helpers\lang;
use Cactuar\Admin\Helpers\recaptcha;

class Root
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {	
		Validator::extend('recaptcha', function($attr, $value, $param) {
			return recaptcha::validate($value);
		});
		Validator::extend('recaptchaV3', function($attr, $value, $param) {
			return recaptcha::validateV3($value);
		});
		
		return $next($request);
    }
}
