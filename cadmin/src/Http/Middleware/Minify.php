<?php

namespace Cactuar\Admin\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Minify
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
        $response = $next($request);
        $buffer = $response->getContent();
        
		$replace = array(
			'/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\n([\S])/"                => ' $1',
                "/\r/"                      => '',
                "/\n/"                      => '',
                "/\t/"                      => ' ',
                "/ +/"                      => ' ',
		);
        
        $buffer = preg_replace(array_keys($replace), array_values($replace), $buffer);
        $response->setContent($buffer);
        //ini_set('zlib.output_compression', 'On'); // If you like to enable GZip, too!
        return $response;
    }
}
