<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\DraftLog;
use Cactuar\Admin\Helpers\helper;

class Draft extends Model
{
	protected $table = 'drafts';
	
	private $initialize = false;
	private $uniqid = '';
	private $module = '';
	private $origin;
	
	public static function initial($uniqid,$module,$origin)
	{
		$res = new Draft;
		$res->uniqid = $uniqid;
		$res->module = $module;
		$res->initialize = true;
		$res->origin = $origin;
		
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
	
	public function translated($key, $lang = null)
    {
        if (is_null($lang))
            $lang = \Cactuar\Admin\Helpers\lang::active();
        
        $res = self::whereModule($this->module)->whereUniqid($this->uniqid)
                    ->whereDraftKey($key)->whereLang($lang)->first();
        if ($res)
            return $res->draft_value;
        return $this->origin->translated($key,$lang);
    }
    
    public function value($key)
    {
        $res = self::whereModule($this->module)->whereUniqid($this->uniqid)
                    ->whereDraftKey($key)->first();
        
        if ($res)
            return $res->draft_value;
        return $this->origin->{$key};
	}
    
    public function meta($key, $lang = '')
    {
        $module = $this->module;
        if (DraftLog::whereModule($this->module)->whereUniqid($this->uniqid)->count()>= 1)
            $module .= '-draft';
        
        if ($meta = MetaData::whereUniqid($this->uniqid)->whereModule($module)->whereLang($lang)->first())
            return $meta->{'meta_'.$key};
        return '';
    }
    
    public static function widget($module,$uniqid,$key,$multiple = true)
    {
        if (DraftLog::whereModule($module)->whereUniqid($uniqid)->count() >= 1)
            $module .= '-draft';
        
        return Widget::initial($uniqid,$module,$key,$multiple);
    }
	
	public function widgetData($key,$multiple = true)
	{
		return self::widget($this->module,$this->uniqid,$key,$multiple);
	}
    
    public static function deleteIfExists($module,$uniqid)
    {
        
    }
}