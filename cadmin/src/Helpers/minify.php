<?php namespace Cactuar\Admin\Helpers;

class minify
{
    private $type = '';
    private static $codes = ['css'=>[],'js'=>[]];
    private static $available = ['css'=>[],'js'=>[]];
    
    public function __construct($type)
    {
        $type = strtolower($type);
        if (!in_array($type,['css','js']))
            throw new \Exception('invalid minify type');
        
        if (empty(self::$codes[$type]))
            self::$codes[$type] = config('cadmin.minify.autoload.'.$type);
        
        if (empty(self::$available[$type])) {
            $res = opendir(public_path(config('cadmin.minify.public-path.'.$type)));
            while ($f = readdir($res)) {
                if (strtolower(pathinfo($f,PATHINFO_EXTENSION)) == $type)
                    array_push(self::$available[$type],$f);
            }
            closedir($res);
        }
        
        $this->type = $type;
    }
    
    public static function initial($type)
    {
        return new minify($type);
    }
    
    public function append($code)
    {
        $codes = is_array($code) ? $code : [$code];
        
        foreach ($codes as $v) {
            if (strtolower(pathinfo($v,PATHINFO_EXTENSION)) != $this->type)
                throw new \Exception('invalid minify append type');
            array_push(self::$codes[$this->type], $v);
        }
        return $this;
    }
    
    public function remove($code)
    {
        $codes = is_array($code) ? $code : [$code];
        
        foreach (self::$codes[$this->type] as $k => $v)
            if (in_array($v, $codes))
                unset(self::$codes[$this->type][$k]);
        return $this;
    }
    
    public function fname()
    {
        return 'minify-'.base64_encode(implode(',',self::$codes[$this->type])).'.min.'.$this->type;
    }
    
    public function url()
    {
        return asset(config('cadmin.minify.public-path.'.$this->type)).'/'.$this->fname();   
    }
    
    public function minifyFile($file)
    {
        $dir = str_replace('\\','/',public_path(config('cadmin.minify.public-path.'.$this->type)));
        
        $path = realpath($dir.'/'.$file);
        
        if (!file_exists($path))
            return '';
        
        if (!in_array($file, self::$available[$this->type]))
            throw new \Exception('invalid file path');
        
        if (str_replace('\\','/', dirname($path)) !== $dir) //prevent '/./', '/../'
            throw new \Exception('invalid file path');
        
        if (strtolower(pathinfo($path,PATHINFO_EXTENSION)) != $this->type) //prevent unrecognize file type
            return new \Exception('invalid minify path type');
        
        $raw = file_get_contents($path);
        
        if ($this->type == 'css')
            return self::minifyCss($raw);
        if ($this->type == 'js') {
            if (substr($file, -7) == '.min.js')
                return $raw;
            else
                return self::minifyJs($raw);
        
        }
        
        return '';
    }
    
    public static function minifyCss($css)
    {
        $css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
        $css = str_replace(': ', ':', $css);
        return str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
    }
    
    public static function minifyJs($js)
    {
        return str_replace(PHP_EOL,'',JSMin::minify($js));
    }
    
    public static function minifyHtml($html)
    {
        $replace = array(
			'/<!--[^\[](.*?)[^\]]-->/s' => '',
                "/<\?php/"                  => '<?php ',
                "/\n([\S])/"                => ' $1',
                "/\r/"                      => '',
                "/\n/"                      => '',
                "/\t/"                      => ' ',
                "/ +/"                      => ' ',
		);
        
        return preg_replace(array_keys($replace), array_values($replace), $html);
    }
    
    public function build($code)
    {
        $codes = explode(',',base64_decode($code));
        if (!is_array($codes))
            return '';
        
        $str = '';
        foreach ($codes as $v)
            $str .= $this->minifyFile($v);
        
        $dir = public_path(config('cadmin.minify.public-path.'.$this->type));
        if (!file_exists($dir))
            throw new \Exception('path not found');
        
        $res = fopen($dir.'/minify-'.$code.'.min.'.$this->type,'w');
        fwrite($res, $str);
        fclose($res);
        
        if ($this->type == 'css')
            header("Content-type: text/css");
        if ($this->type == 'js')
            header('Content-Type: application/javascript');
        echo $str;
        die();
    }
}

?>