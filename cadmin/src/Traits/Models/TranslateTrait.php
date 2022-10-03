<?php namespace Cactuar\Admin\Traits\Models;

use Cactuar\Admin\Helpers\lang;

trait TranslateTrait
{
	public function scopeTranslate($q, $table = '', $tableAlias = '')
	{
		if (!$table)
			$table = $this->table;
        
        if (!$tableAlias)
            $tableAlias = $this->table;
		
		$transTable = self::transTable($table);
		
		return $q->leftJoin($transTable, function($q) use ($table, $transTable, $tableAlias) {
			$q->on($tableAlias.'.id', '=', $transTable.'.base_id')
				->where($transTable.'.lang', '=', (string) lang::active());
		});
	}
	
	public function translated($field, $lang = '')
	{
		if (!$this->id)
			return '';
			
		if (!$lang)
			$lang = lang::active();
		
		$res = \DB::table($this->transTable)->select($field)
				->whereBaseId($this->id)->whereLang($lang)->first();
				
		if (!$res)
			return '';
			
		return $res->{$field};
	}
	
	public function getTransTableAttribute()
	{
		return self::transTable($this->getTable());
	}
	
	public static function transTable($table)
	{
		return $table.'_lang';
	}
}
