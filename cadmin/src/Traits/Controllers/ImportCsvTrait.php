<?php namespace Cactuar\Admin\Traits\Controllers;

use Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Models\Log;

trait ImportCsvTrait
{
    public function getImport()
    {
        $module = $this->module();
        
        return view('cactuar::admin.import', ['mainAction' => $this->baseMenu($module, 'import'), 'append' => $this->importAppend()]);
    }
    
    public function importMainMenu($menu)
	{
		return $menu;
	}
    
    public function postImport(Request $request)
    {
        $module = $this->module();
        
        $this->validate($request,
        [
            'separator' => 'required',
            'csv' => 'required|file|max:2048|mimetypes:application/vnd.ms-excel,text/plain,text/csv,text/tsv',
        ]);
        
        $separator = Request::input('separator');
        if (!in_array($separator, [',', ';']))
            abort(404);
        
        $success = $failed = 0;
        
        $idx = 1;
        $res = fopen(Request::file('csv')->getPathName(), 'r');
        while ($data = fgetcsv($res, 1024, $separator)) {
            $status = $this->importCallback($idx, $data);
            
            if ($status === true)
                $success++;
            
            if ($status === false)
                $failed++;
            
            $idx++;
        }
        
        fclose($res);
        
        $log = 'Success import:'.$success.' success, '.$failed.' failed';
        Log::write($module, 'import', null, $log);
        
        return back()->with('success', $log);
    }
    
    public function importCallback($idx,$item)
    {
        return false;
    }
    
    public function importAppend()
    {
        return '';   
    }
}