<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Models\WidgetDetail;
use Cactuar\Admin\Helpers\helper;

class Widget extends Model
{
	protected $table = 'widgets';
	protected $initialize = false;
    
	public function widgetDetail()
	{
		return $this->hasMany(WidgetDetail::class);
	}
    
    public static function initial($uniqid, $module, $key, $multiple = true)
    {
        $res = self::whereUniqid($uniqid)->whereModule($module)->where('key', $key);
        
        if ($multiple) {
            $res = $res->orderBy('id','asc')->get(); 
            foreach ($res AS &$v) {
                $v->initialize = true;
            }
        } else {
            $res = $res->first();
            if (!$res)
                return $res;
            $res->initialize = true;
        }
        
        return $res;
    }
    
    public function childs($key, $multiple = true)
    {
        $res = self::whereUniqid($this->uniqid)->whereModule($this->module)->with('widgetDetail')->whereParentId($this->id)->where('key',$key);
        
        if ($multiple) {
            $res = $res->orderBy('id','asc')->get(); 
            foreach ($res AS &$v) {
                $v->initialize = true;
            }
        } else {
            $res = $res->first();
            if (!$res)
                return $res;
            $res->initialize = true;
        }
        
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
    
    public function translated($key, $lang = '') 
    {
        if (!$lang)
            $lang = \Cactuar\Admin\Helpers\lang::active();
        
    	return $this->value($key, $lang);
	}
    
    public function value($key, $lang = '')
    {
        $res = $this->widgetDetail()->whereFieldName($key)->whereLang($lang);
        
        $res = $res->first();
		
        if ($res)
            return $res->val;
        
        return null;
    }
}