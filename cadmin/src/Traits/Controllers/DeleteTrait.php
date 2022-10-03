<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\WidgetDetail;
use Cactuar\Admin\Helpers\adminForm;
use DB;

trait DeleteTrait
{
    
    public function getDelete()
    {
        $module = $this->module();
		
		$r = [];
		$unique = request()->get('unique');
		
        if (is_array($unique)) {
            foreach ($unique AS $v) {
				$item = $this->res('delete')->findOrFail($v);
				if (!$this->deleteAble($item))
					return back()->with('error', 'one or more item(s) cannot be deleted');
			
				array_push($r, $item);
            }
        } else {
            $item = $this->res('delete')->findOrFail($unique);
			if (!$this->deleteAble($item))
				return back()->with('error', 'one or more item(s) cannot be deleted');
			
			array_push($r, $item);
        }
		
		foreach ($r AS $item) {
			$log = $this->log($item, 'delete');
            
			$id = $item->id;
			$row = clone $item;
			
			if ($this->deleteCallbackBefore($row) !== true)
				return back()->with('error', 'Failed delete data');
			
            adminForm::initial($module)->delete($item, function($item) use($row) { return $this->deleteCallbackAfter($row); });
            
            event(new \Cactuar\Admin\Events\CudAfter($module, $row, 'delete'));

			Log::write($module, 'delete', $id, $log);
		}
        
        return back()->with('success', count($r).' selected item(s) has been deleted');
    }
    
    public function deleteAble($item)
    {
        return true;
    }
    
	public function deleteCallbackBefore($item)
	{
		return true;
	}
	
    public function deleteCallbackAfter($item)
    {
        return true;
    }
	
	public function deleteRes()
	{
		return null;
	}
	
	public function deleteLog($item)
	{
		return '';
	}
    
    public function deleteBulkAction(&$obj)
    {
        $module = $this->module();
        
        if (!Auth::user()->allow($module, 'delete'))
            return null;
        
        array_push($obj, [
            'target' =>  \Cactuar\Admin\Helpers\admin::url($module.'/delete/'),
            'label' => 'Delete',
            'prompt' => 'Are you sure want to delete selected item(s)?'
        ]);
    }
}