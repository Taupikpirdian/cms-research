<?php namespace Cactuar\Admin\Helpers;

use Illuminate\Support\Facades\Session;
use Intervention\Image\ImageManagerStatic as Image;
use Config;

class media 
{
	protected $raw = [];
    
    public function __construct($raw)
    {
        $this->raw = json_decode($raw,true);
        if (json_last_error() != JSON_ERROR_NONE || !is_array($this->raw))
            $this->raw = [];
    }
    
    public static function initial($raw)
    {
        return new media($raw);
    }
	
	public function getAlt($lang = '')
	{
		if (!$lang)
			$lang = lang::active();
		return $this->{'alt_'.$lang};
	}
    
    public function __get($key)
    {
        if ($key == 'url')
            return asset(config('cadmin.media.sourcepath').'/'.array_get($this->raw,'path'));
        if ($key == 'html')
            return '<img src="'.$this->url.'" alt="'.$this->alt.'">';
        
        if ($key == 'sizeReadable')
            return helper::formatBytes($this->size);
		
		if ($key == 'alt')
			return $this->getAlt();
        
        return array_get($this->raw,$key);
    }
	
    public static function thumbPath($source, $width, $height, $type = 'cover')
    {
        $fname = basename($source);
        $fpath = dirname($source);
			
		$info = pathinfo($source);
		
        return Config::get('cadmin.media.thumbpath').'/'.$fpath.'/thumb_'.$width.'_'.$height.'_'.$type.'_'.$fname;
    }
    
    public static function thumb($source, $key)
    {
		$source = media::initial($source)->path;
        $conf = self::conf($key); 
        
        if (!is_array($conf))
            throw new \Exception('Media type unknown');
        
        $width = array_get($conf, 'width');
        $height = array_get($conf, 'height');
        $type = array_get($conf, 'type');
        
        if (!in_array($type, ['contain', 'cover']))
            $type = 'cover';
        
        $target = self::thumbPath($source, $width, $height, $type);
        if (!file_exists($target)) { 
            $ses = session('cfind-thumb-req');
            if (!is_array($ses))
                $ses = [];
            if (!array_key_exists($target,$ses)) {
                array_push($ses,$target);
                session()->put('cfind-thumb-req',$ses);
            }
        }
            
        return asset($target);
    }
    
    public static function thumbMake()
    {
        //check if file exists
        $ori = implode('/',request()->segments());
        if (file_exists(public_path($ori)))
            return public_path($ori);
        
        //validate by session
        $ses = session('cfind-thumb-req');
        if (!is_array($ses) || !in_array($ori,$ses))
            die('Forbidden Access');
        
        foreach($ses as $k=>$v)
            if ($v == $ori)
                unset($ses[$k]);
        session()->put('cfind-thumb-req',$ses);
        
        //extract path & file name
        $fpath = trim(substr($ori, strlen(config('cadmin.media.thumbpath')), strlen($ori)),'/');
        $path = dirname($fpath);
        $file = basename($fpath);
        
        //extract original file name
        $x = explode('_',$file);
        $res['thumb'] = array_shift($x);
        $res['width'] = array_shift($x);
        $res['height'] = array_shift($x);
        $res['type'] = array_shift($x);
        $res['source'] = $path.'/'.implode('_',$x);
        
        //process thumb make
        if (!in_array($res['type'], ['contain', 'cover']))
            $res['type'] = 'cover';
        
        if (
            !isset($res['source']) 
            ) {
            die('Forbidden Access');
        }
        
        $source = Config::get('cadmin.media.sourcepath').'/'.$res['source'];
        if (!file_exists($source))
            die('Forbidden Access');
        
		if (!in_array(pathinfo($source, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'bmp', 'png', 'gif']))
            die('Forbidden Access');
		
        if (!$res['width'] && !$res['height'])
            die('Forbidden Access');
        
        $target = self::thumbPath($res['source'], $res['width'], $res['height'], $res['type']);
        if (file_exists($target))
            return public_path($target);
        
        self::path(dirname($target));
		
		$img = Image::make($source);
		
		if ($res['width'] == 'auto' || !$res['width']) {
			$img->resize(null, $res['height'], function($constraint) {
				$constraint->aspectRatio();
			});
			$img->save($target);
            
            return public_path($target);
		}
		
		if ($res['height'] == 'auto' || !$res['height']) {
			$img->resize($res['width'], null, function($constraint) {
				$constraint->aspectRatio();
			});
			$img->save($target);
            
            return public_path($target);
		}
        
        if ($res['type'] == 'cover') {
              
			$img->fit($res['width'], $res['height']);
            $img->save($target);
            
            return public_path($target);
        }
        
        if ($res['type'] == 'contain') {
			
			if ($img->height() > $img->width()) {
				if ($img->height() > $res['height']) {
					$img->resize(null, $res['height'], function ($constraint) {
						$constraint->aspectRatio();
					});
				}
				if ($img->width() > $res['width']) {
					$img->resize($res['width'], null, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
			} else {
				if ($img->width() > $res['width']) {
					$img->resize($res['width'], null, function ($constraint) {
						$constraint->aspectRatio();
					});
				}
				if ($img->height() > $res['height']) {
					$img->resize(null, $res['height'], function ($constraint) {
						$constraint->aspectRatio();
					});
				}
			}
            
			$canvas = Image::canvas($res['width'], $res['height']);
            
            $canvas->insert($img, 'center');
            $canvas->save($target);
            
            return public_path($target);
        }
    }
    
    public static function path($path) {
		
		if (!file_exists($path)) 
			mkdir($path, 0775, true);
			
		if (!file_exists($path.'/index.html')) {
			$res = fopen($path.'/index.html', 'w');
			fwrite($res, 'FORBIDEN!');
			fclose($res);
		}
		
		return $path;
	}
    
    public static function url($path)
    {
        return media::initial($path)->url;
    }
	
	public static function convert($path,$ext)
	{
		return asset(config('cadmin.media.convertpath').'/'.media::initial($path)->{'convert-'.$ext});
	}
    
    public static function source($path)
    {
        return public_path(config('cadmin.media.sourcepath').'/'.media::initial($path)->path); //Config::get('cadmin.media.sourcepath').'/'.$path;
    }
	
	public static function conf($key)
	{
		if ($key == 'favicon')
			return [
				'width' => 32,
				'height' => 32,
				'type' => 'contain',
			];
			
		return Config::get('cadmin.media.thumbs.'.$key);	
	}
    
    public static function info($key)
    {	
        $res = Config::get('cadmin.media.thumbs.'.$key);
        if (!is_array($res))
            return '';
        $out = [];
        if (array_get($res, 'width'))
            $out[] = 'width : '.$res['width'];
        
        if (array_get($res, 'height'))
            $out[] = 'height : '.$res['height'];
        
        return implode(' x ', $out);
    }   
}