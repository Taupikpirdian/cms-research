<?php namespace Cactuar\Admin;

/* KERANGKA DULU YA */

class CactuarValidate
{
    
    private $rules = [];
    private $nice = [];
    
    public static function make()
    {
        return new CactuarValidate();
    }
    
    public static function niceName($key)
    {
        //create nice name here
        
        return $key;
    }
    
    public function set($key, $rules, $niceName = '', $multilang = false, $widget = false)
    {
        if (!$niceName)
            $niceName = self::niceName($key);
        
    }
    
    public function widgetMin($key, $min = 1, $name = '')
    {
        
    }
    
    public function build($post = null)
    {
        if ($post === null)
            $post = \Request::input();
        
        return Validator::make($post, $this->rules, $this->nice);
    }
    
    public function render($post = null)
    {
        if ($post === null)
            $post = \Request::input();
        
        $valid = Validator::make($post, $this->rules, $this->nice);
        if (!$valid->fails())
            return true;
        
        return $valid->errors();
    }
}