<?php namespace Cactuar\Admin\Helpers;

use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Helpers\media;
use Cactuar\Admin\Models\Conf;

class og {
    
    private static $og = [];
    
    public static function set($key, $val, $force = false) {
        if (!$force && array_key_exists($key, self::$og)) 
            return;
        
        self::$og[$key] = $val;    
    }
    
    public static function sets($item)
    {
        /*foreach (['label', 'name', 'title'] AS $key) {
            if ($item->{$key})
                self::set('title', $item->{$key}, true);
        }*/
        
        if ($item->meta_title)
            self::set('title', $item->meta_title, true);
        if ($item->author)
            self::set('author', $item->author);
        if ($item->meta_description) 
            self::set('description', $item->meta_description, true);
        if ($item->meta_keywords)
            self::set('keywords', $item->meta_keywords, true);
        if ($item->meta_image)
            self::set('image', media::url($item->meta_image), true);
        
        if ($item->description) 
            self::set('description', $item->description, true);
    }
    
    public static function render($code = 'site-setting')
    {
        $config = Conf::initial($code);
        
        foreach (['title', 'description', 'keywords', 'image', 'copyright', 'url', 'fb-app-id', 'twitter-id'] AS $key) {
            if (!array_key_exists($key, self::$og)) {
                if (in_array($key, ['title','description','keywords']))
                    self::$og[$key] = $config->translated('meta-'.$key);
                else if ($key == 'image')
                    self::$og[$key] = $config->value('meta-'.$key) ? media::url($config->value('meta-'.$key)) : '';
                else if ($key == 'url')
                    self::$og[$key] = Request::url();        
                else
                    self::$og[$key] = $config->value('meta-'.$key);
            }
        }
        
        /*if (!self::get('title')) {
            self::$og['title'] = $config->value('title');
        }*/
        
        $og = '
<title>'.self::get('title').'</title>
<meta name="title" content="'.self::get('title').'">
<meta name="description" content="'.self::get('description').'">
<meta name="keywords" content="'.self::get('keywords').'">
<meta name="url" content="'.self::get('url').'">
<meta name="image" content="'.self::get('image').'">
<meta name="fb::app_id" content="'.self::get('fb-app-id').'">
<meta name="article:author" content="'.self::get('author').'">

<meta name="og:type" content="website">
<meta name="og:title" content="'.self::get('title').'">
<meta name="og:description" content="'.self::get('description').'">
<meta name="og:url" content="'.self::get('url').'">
<meta name="og:image" content="'.self::get('image').'">
<meta name="og:author" content="'.self::get('author').'">

<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="'.self::get('title').'">
<meta name="twitter:description" content="'.self::get('description').'">
<meta name="twitter:twitter-id" content="'.self::get('twitter-id').'">
<meta name="twitter:image" content="'.self::get('image').'">
'.$config->value('analytic-scripts').PHP_EOL.
$config->value('schema').PHP_EOL.
$config->value('head-scripts').PHP_EOL;//.self::get('conversion-script');
        
		$favicon = $config->favicon;
		
		if (media::source($favicon)) {
			if (strtolower(pathinfo(media::source($favicon), PATHINFO_EXTENSION)) == 'png') {
				$og .= '<link rel="icon" type="image/png" href="'.media::thumb($favicon, 'favicon').'" sizes="32x32">'.PHP_EOL;
			}
			
			if (strtolower(pathinfo(media::source($favicon), PATHINFO_EXTENSION)) == 'ico') {
				$og .= '<link rel="icon" href="'.media::url($favicon).'">'.PHP_EOL;
			}
		}
        
        if (array_key_exists('canonical',self::$og))
            $og .= '<link rel="canonical" href="'.e(url(self::$og['canonical'])).'" />'.PHP_EOL;
		else
			$og .= '<link rel="canonical" href="'.e(url(implode('/',request()->segments()))).'" />'.PHP_EOL;
        if (array_key_exists('conversion-script', self::$og))
            $og .= self::$og['conversion-script'].PHP_EOL;
        
        if (!in_array(config('app.env'),['production','live']))
            $og .= '<meta name="robots" content="noindex">';
        
        return $og;
    }
    
    public static function body($code = 'site-setting')
    {
        $config = Conf::initial($code);
        $og = $config->value('body-scripts');
    
        return $og;
    }
    
    public static function copyright($code = 'site-setting')
    {
        $copyright = Conf::initial($code)->value('copyright');
        foreach ([
            'year' => date('Y'),
            'webarq' => 'Site by <a href="https://www.webarq.com/" target="_blank">WEBARQ</a>',
        ] as $k=>$v)
            $copyright = str_replace('['.$k.']',$v,$copyright);
        
        return $copyright;
    }
    
    public static function get($key) 
    {
        if (!array_key_exists($key, self::$og)) return '';
        return htmlspecialchars(strip_tags(self::$og[$key]));
    }
    
}

?>
