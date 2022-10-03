<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Helpers\lang;
use Cactuar\Admin\Helpers\helper;

class Conf extends Model
{
	private $module = null;
	protected $initialize = false;
	private static $temp = [];
	
	protected $table = 'configs';
    public $timestamps = false;
    
	public static function initial($module)
	{
		$res = new self();
        $res->initialize = true;
        $res->module = $module;
        
        return $res;
	}
	
	public function __get($key)
	{
        $base = parent::__get($key);
        if ($base)
            return $base;
        
        if ($this->initialize) {
            $key = helper::camel2dashed($key);
            
            //hanya untuk lang NULL. paksa penggunaan translated untuk data yang multiplelang
            $val = $this->value($key);
            if (!is_null($val))
                return $val;
        }
        
        return $base;
    }
    
    public function widget($key,$multiple=true)
    {
        if (!$this->initialize)
            return [];
        
        return widget(99,$this->module.'-conf',$key,$multiple);
    }
	
    public function translated($key, $lang = null)
    {
        if (is_null($lang))
            $lang = \Cactuar\Admin\Helpers\lang::active();
        
        if (!$this->module)
            return '';
        
        return $this->value($key, $lang);        
    }
    
    public function value($key, $lang = '')
    {
        return self::_value($this->module, $key, $lang);
    }
    
    public static function _value($module, $key, $lang = '')
    {
        $idx = $module.'::'.$key.'::'.$lang;
        
        //check temp
        if (array_key_exists($idx, self::$temp))
            return self::$temp[$idx];
        
        $res = self::whereModule($module)
                    ->where('key', $key)->whereLang($lang);
        
        $res = $res->first();
        if (!is_null($res)) {
            self::$temp[$idx] = $res->val;
            return $res->val;
        }
            
        return null;
    }
}