<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Traits\Models\TranslateTrait;
use Cactuar\Admin\Traits\Models\SortableTrait;
use Cactuar\Admin\Traits\Models\PermalinkTrait;
use Cactuar\Admin\Traits\Models\ModelHelperTrait;
use Cactuar\Admin\Models\Related;
use Request;

class Menu extends Model
{
    use TranslateTrait, SortableTrait, PermalinkTrait, ModelHelperTrait;
    
    public $table = 'menus';
    protected $sortParent = 'parent_id';
	protected $module = 'menu';
    
	public function childs()
	{
		return $this->hasMany(Menu::class,'parent_id');
	}
	
	public function scopeFe($q)
	{
		return $q->translate()->whereIsActive(1)->orderBy('sort_id')->orderBy('id');
	}
	
    public function getFullUrlAttribute()
    {
        if ($this->type == 'url')
            return $this->getOriginal('url');
        if ($this->type == 'blank') {
            $child = Menu::select('type','id','url')->whereParentId($this->id)->whereIsActive(1)->orderBy('sort_id')->first();
            if ($child)
                return $child->fullUrl;
            return '';
        }
        
        return url($this->permalink);
    }
    
    public function getBreadsAttribute()
    {
        $parentId = $this->parent_id;
        $breads = [$this];
        
        while ($parentId > 0) {
            $parent = Menu::translate()->select('id','label','type','url','parent_id')->whereId($parentId)->first();
            if (!$parent)
                break;
            $breads[] = $parent;
            $parentId = $parent->parent_id;
        }
        
        return array_reverse($breads);
    }
    
    public function getActiveIdsAttribute()
    {
        $ids = [];
        foreach ($this->breads as $v)
            $ids[] = $v->id;
        return $ids;
    }
    
    public function getRootIdAttribute()
    {
        foreach ($this->breads as $v)
            return $v->id;
        return $this->id;
    }
	
	public function getSortIdsAttribute()
    {
        if (!$this->parent_id)
            return $this->sort_id;
        $parent = self::findOrFail($this->parent_id);
        
        $string = $this->sort_id.'.'.$parent->sortIds;
        $string = trim(implode('.',array_reverse(explode('.',$string))),'.');
        return "$string";
    }
    
    public static function position($position)
    {
        $out = [];
        foreach (Related::whereModule('menu')->whereRelated('position')->whereRelatedUniqid($position)->get() as $v) {
            if ($menu = self::translate()->whereIsActive(1)->find($v->uniqid)) {
                $out[$menu->sortIds] = $menu;
            }
        }
        ksort($out);
        return $out;
    }
}
