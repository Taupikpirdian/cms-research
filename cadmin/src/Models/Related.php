<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class Related extends Model
{
	protected $table = 'relateds';
	public $timestamps = false;
	
	public static function selected($uniqid, $module, $related, $reverse = false)
	{
		$out = [];
		if (!$reverse) {
			$res = self::select('related_uniqid as uid')->whereUniqid($uniqid)->whereModule($module)->whereRelated($related);
		} else {
			$res = self::select('uniqid as uid')->whereRelatedUniqid($uniqid)->whereModule($related)->whereRelated($module);
		}
		
		foreach ($res->get() AS $v) {
			array_push($out, $v->uid);
		}
		
		return $out;
	}
}