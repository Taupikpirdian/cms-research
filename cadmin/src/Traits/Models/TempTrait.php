<?php namespace Cactuar\Admin\Traits\Models;

trait TempTrait
{
    protected static $temp = [];
    
    public function scopeUnique($q, $id)
    {
        return $q->whereId($id);
    }
    
    public static function temp($id, $key = 'name')
    {
        if (array_key_exists($id, self::$temp))
            return self::$temp[$id]->{$key};
        
        $res = self::unique($id)->first();
        if (!$res)
            return '';
        
        self::$temp[$id] = $res;
        
        return self::$temp[$id]->{$key};
    }
}