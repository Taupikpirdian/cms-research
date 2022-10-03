<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Cactuar\Admin\Models\Log;

trait ReadTrait
{
	public function getRead()
	{
        $module = $this->module();
        
        $act = Request::input('read') ? 'read' : 'unread';
        
        $unique = Request::input('unique');
        if (!is_array($unique))
            $unique = [$unique];
        
        $r = [];
        foreach ($unique as $k => $v) {
            $item = $this->listingRes('read')->findOrFail($v);
            if ($this->readAble($item) !== true) 
                return back()->with('error', 'one or more item(s) are invalid');
            
            array_push($r, $item);
        }
        
        $success = 0;
        foreach ($r AS $item) {
			$log = $this->log($item, 'read');
            
			$id = $item->id;
			$row = clone $item;
			
			if (
                ($act == 'read' && $row->is_read)
                || ($act == 'unread' && !$row->is_read)
                )
                continue;
            
            if ($act  == 'read')
                $row->is_read = 1;
            
            if ($act == 'unread')
                $row->is_read = 0;
            
            $row->save();
            
			Log::write($module, $act, $id, $log);
            $success++;
		}
        
        return back()->with('success', $success.' selected item(s) has been marked as '.$act);
    }
	
	public function readAble($item)
	{
		return true;
	}
    
    public function readBulkAction(&$obj)
    {
        $module = $this->module();
        
        if (!Auth::user()->allow($module, 'read'))
            return null;
        
        array_push($obj,  [
            'target' =>  \Cactuar\Admin\Helpers\admin::url($module.'/read?read=1&'),
            'label' => 'Mark as read',
            'prompt' => 'Are you sure want mark selected item(s) as read?'
        ]);
        
        array_push($obj,  [
            'target' =>  \Cactuar\Admin\Helpers\admin::url($module.'/read?read=0&'),
            'label' => 'Mark as unread',
            'prompt' => 'Are you sure want mark selected item(s) as unread?'
        ]);
    }
}
?>