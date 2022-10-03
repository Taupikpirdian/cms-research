<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Cactuar\Admin\Models\User;

class Log extends Model
{
    protected $table = 'logs';
    
    public static function write($module, $act, $unique = null, $uniqueLabel = null)
    {
        $res = new Log();
        $res->user_id = Auth::user()->id;
        $res->username = Auth::user()->name;
        $res->user_display_name = Auth::user()->display_name;
        $res->module = $module;
        $res->act = $act;
        $res->unique_id = $unique;
        $res->unique_label = $uniqueLabel;
        $res->post = json_encode(self::postData());
        
        $res->save();
    }
    
    public static function postData()
    {
        $post = \Request::except('_token');
        
        foreach ($post AS $key => &$val) {
			if (in_array($key, array('password', 'old_pass')))
				$val = '';
				
            if (!is_array($val)) {
                $val = str_limit($val, 50);
            }
        }
        
        return $post;
    }
}
