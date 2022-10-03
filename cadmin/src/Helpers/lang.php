<?php namespace Cactuar\Admin\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Lang as LaravelLang;
use Config;
use Route;
use App;

class lang
{
	protected static $active = null;
    protected static $translations = [];
    protected static $avail = [];
	
    public static function initial()
    {
        if (self::$active)
            return self::$active;
        
        $lang = self::codes();
        $current = self::defaultCode();
        
        if (in_array(Request::segment(1), $lang))
			$current = Request::segment(1);
    
        self::active($current);
    }
    
    public static function active($code = null)
	{
		if (self::$active && self::$active == $code)
            return self::$active;
        
        $lang = self::codes();
		
        if ($code && in_array($code, $lang))
            self::$active = $code;
        
        $active = null;
        
        if (self::$active && in_array(self::$active, $lang))
            $active = self::$active;
        
        if (!$active)
            $active = self::defaultCode();
        
        if ($code && $active) {
            $lctime = config('cadmin.lang.lc_time.'.$active);
            if (!$lctime)
                $lctime = 'id_ID';
            setlocale(LC_TIME, $lctime);
        }
		
		if (App::getLocale() != $active)
            App::setLocale($active);
        
        return $active;
	}
	
	public static function defaultCode()
	{
		$codes = self::codes();
		$default = config('cadmin.lang.default');
		
		if ($default && in_array($default, $codes))
			return $default;
		
		return reset($codes);
	}
	
	public static function codes()
	{
		$codes = config('cadmin.lang.codes');
		if (!is_array($codes))
			return [];
		
		return $codes;
	}
	
	public static function translate($code, $param = [], $lang = null)
	{
		if ($lang == null)
			$lang = self::active();
		
        if (!\Schema::hasTable('translations'))
		    return LaravelLang::get('site.'.$code, $param, $lang);
        
		if (!array_key_exists($lang, self::$translations))
			self::$translations[$lang] = [];
		
		$translation = '';
        
        if (array_get(self::$translations, $lang.'.'.$code))
			$translation = array_get(self::$translations, $lang.'.'.$code);
		else {
			$res = \Cactuar\Admin\Models\Translation::whereLang($lang)->whereCode($code)->first();
			if ($res && $res->id)
				self::$translations[$lang][$code] = $translation = $res->translation ? $res->translation : 'null';
		}
        
        if ($translation) {
            if ($translation == 'null')
                return '';
			foreach ($param as $k => $v) {
				$translation = str_replace(':'.$k, $v, $translation);
			}
			return $translation;
		}
		
		return LaravelLang::get('site.'.$code, $param, $lang);
	}
    
    public static function setUrl($lang,$url)
    {
        self::$avail[$lang] = $url;
    }
    
	public static function urlChange($lang)
	{
        if (array_key_exists($lang,self::$avail))
            return self::$avail[$lang];
        
		$lang = strtolower($lang);
		$segments = Request::segments();
		$codes = Config::get('cadmin.lang.codes');
		
		if (count($segments) >= 1 && in_array($segments[0], $codes)) 
			$segments[0] = $lang;
		else
			array_unshift($segments, $lang);
		
        if ($segments[0] == Config::get('cadmin.lang.default'))
            unset($segments[0]);
        
		$out = implode('/', $segments);
		
		if (!empty(request()->query()))
			$out .= '?'.http_build_query(Request::input());
		
		return url($out);
	}
    
    public static function url($url)
	{
		if (strpos(' '.$url, '//') != false && strpos($url, '.') != false)
			return $url;
		
        if (self::active() == Config::get('cadmin.lang.default'))
            return url(trim($url, '/'));
        
		return url(self::active().'/'.trim($url, '/'));
	}
    
    public static function buildRoutes($get=[],$post=[])
    {
        function route($o)
        {
            $out = [];
            foreach ($o as $k => $v) {
                foreach(config('cadmin.lang.codes') as $lang) {
                    
                    if ($lang == config('cadmin.lang.default'))
                        $lang = '';
                    else
                        $lang .= $k ? '/' : '';
                    
                    $out[$lang.$k] = $v;
                }
            }
            
            return $out;
        }
        
        foreach (route($get) as $k => $v) {
            Route::get($k,$v);
        }
        foreach (route($post) as $k => $v) {
            Route::post($k,$v);
        }
    }
	
	public static function routes($callback)
	{
		if (\Schema::hasTable('permalinks')) //make sure lang set by permalink before manual initial
			\Cactuar\Admin\Models\Permalink::active();
		
		self::initial();
		
		Route::group(['prefix' => self::active() != self::defaultCode() ? lang::active() : null],function() use($callback) {
			$callback();
		});
	}
}
