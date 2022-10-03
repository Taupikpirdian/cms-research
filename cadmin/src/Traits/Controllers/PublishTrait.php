<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Cactuar\Admin\Models\Log;
use Illuminate\Support\Facades\Schema;

trait PublishTrait
{
	public function getPublish()
	{
        $module = $this->module();
        
        $act = Request::input('publish') ? 'publish' : 'unpublish';
        
        $unique = Request::input('unique');
        if (!is_array($unique))
            $unique = [$unique];
        
        $r = [];
        foreach ($unique as $k => $v) {
            $item = $this->listingRes('publish')->findOrFail($v);
            if ($this->publishAble($item) !== true) 
                return back()->with('error', 'one or more item(s) are invalid');
            
            array_push($r, $item);
        }
        
        $success = 0;
        foreach ($r AS $item) {
			$log = $this->log($item, 'publish');
            
			$id = $item->id;
			$row = clone $item;
			
			if (
                ($act == 'publish' && $row->is_active)
                || ($act == 'unpublish' && !$row->is_active)
                )
                continue;
            
            if ($act  == 'publish')
                $row->is_active = 1;
            
            if ($act == 'unpublish')
                $row->is_active = 0;
            
            if(Schema::hasTable('permalinks')){
                \Cactuar\Admin\Models\Permalink::whereModule($module)->whereUniqid($row->id)->update(['is_active' => $row->is_active]);
            }
            $row->save();

            event(new \Cactuar\Admin\Events\CudAfter($module,$row,'publish'));
			
			Log::write($module, $act, $id, $log);
            $success++;
		}
        
        return back()->with('success', $success.' selected item(s) has been '.$act.'ed');
    }
	
	public function publishAble($item)
	{
		return true;
	}
    
    public function publishBulkAction(&$obj)
    {
        $module = $this->module();
        
        if (!Auth::user()->allow($module, 'edit'))
            return null;
        
        array_push($obj,  [
            'target' =>  \Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=1&'),
            'label' => 'Mark as publish',
            'prompt' => 'Are you sure want mark selected item(s) as publish?'
        ]);
        
        array_push($obj,  [
            'target' =>  \Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=0&'),
            'label' => 'Mark as unpublish',
            'prompt' => 'Are you sure want mark selected item(s) as unpublish?'
        ]);
    }
}
?>