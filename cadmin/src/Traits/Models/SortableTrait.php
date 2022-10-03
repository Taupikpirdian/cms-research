<?php namespace Cactuar\Admin\Traits\Models;

trait SortableTrait
{
	public function getMaxSortAttribute()
	{
		if (isset($this->sortParent))
			$count = self::where($this->sortParent, $this->{$this->sortParent})->count();
		else {
			$count = self::count();
		}
		
		if ($count < 1)
			return 1;
			
		return $count;
	}
	
	public function reorder($origin, $type = 'update')
	{
		if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($this)))
			throw new Exception('Maaf, kami takut fungsi kami tidak bisa berjalan bersama fungsi softDeletes. Harap disable terlebih dahulu fungsi softDeletes pada model Anda');
			
		if ($this->sort_id > $this->maxSort) {
			$this->sort_id = $this->maxSort;
			$this->save();
		}
		
		if ($this->sort_id < 1) {
			$this->sort_id = $this->maxSort;
			$this->save();
		}
		
		if ($type == 'create') {
			$res = self::where('sort_id', '>=', $this->sort_id)
						->where('id', '!=', $this->id);
			
			if (isset($this->sortParent))
				$res->where($this->sortParent, $this->{$this->sortParent});
			
			return $res->increment('sort_id');
		}
		
		if (!array_key_exists('sort_id', $origin))
			throw new Exception('Cannot find origin sort id');
			
		if (isset($this->sortParent) && !array_key_exists($this->sortParent, $origin))
			throw new Exception('Cannot find origin sort sortParent');
		
		if (isset($this->sortParent)) {
			if ($this->sort_id == $origin['sort_id'] && $this->{$this->sortParent} == $origin[$this->sortParent])
				return true;
			
			if ($this->{$this->sortParent} != $origin[$this->sortParent]) {
				self::where($this->sortParent, $this->{$this->sortParent})
					->where('id', '!=', $this->id)
					->where('sort_id', '>=', $this->sort_id)->increment('sort_id');
				
				self::where($this->sortParent, $origin[$this->sortParent])
					->where('id', '!=', $this->id)
					->where('sort_id', '>=', $origin['sort_id'])->decrement('sort_id');
			} else {
				if ($this->sort_id > $origin['sort_id']) {
					self::where($this->sortParent, $this->{$this->sortParent})
						->where('id', '!=', $this->id)
						->where('sort_id', '>=', $origin['sort_id'])
						->where('sort_id', '<=', $this->sort_id)
						->decrement('sort_id');
				} else {
					self::where($this->sortParent, $this->{$this->sortParent})
						->where('id', '!=', $this->id)
						->where('sort_id', '>=', $this->sort_id)
						->where('sort_id', '<=', $origin['sort_id'])
						->increment('sort_id');
				}
			}
		} else {
			if ($this->sort_id > $origin['sort_id']) {
				self::where('sort_id', '>=', $origin['sort_id'])
					->where('id', '!=', $this->id)
					->where('sort_id', '<=', $this->sort_id)
					->decrement('sort_id');
			} else {
				self::where('sort_id', '>=', $this->sort_id)
					->where('id', '!=', $this->id)
					->where('sort_id', '<=', $origin['sort_id'])
					->increment('sort_id');
			}
		}
			
		return true;
	}	
	
	public function reorderByDelete($origin)
	{
		if (!array_key_exists('sort_id', $origin))
			throw new Exception('Cannot find origin sort id');
			
		if (isset($this->sortParent) && !array_key_exists($this->sortParent, $origin))
			throw new Exception('Cannot find origin sort sortParent');
			
		$res = self::where('sort_id', '>=', $origin['sort_id']);
		
		if (isset($this->sortParent))
			$res->where($this->sortParent, $origin[$this->sortParent]);
			
		return $res->decrement('sort_id');
	}
	
	public function orderUp()
	{
		$origin = $this->getOriginal();
		$this->increment('sort_id');
		
		return $this->reorder($origin, 'update');
	}
	
	public function orderDown()
	{
		$origin = $this->getOriginal();
		$this->decrement('sort_id');
		
		return $this->reorder($origin, 'update');
	}
	
	public function resetOrder()
	{
		//do reset order of all record
	}
	
}