<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Config;
use Cactuar\Admin\Helpers\helper;
    
trait DownloadTrait
{	
    public function getDownload()
    {   
        $data = $this->_listingCompile('download');
        
		$csv = [];
        array_push($csv, $data['head']);
        foreach ($data['data'] AS $k => $v) {
            if (array_key_exists('uniqid', $v)) {
                unset($v['uniqid']);
            }
            array_push($csv, $v);
        }
        
        return helper::csv($csv, 'log');
    }
}