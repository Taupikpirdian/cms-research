<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Models\Permalink;
use Config;

class ValidateController extends Controller
{
    public function postUnique()
    {
        if (!Request::get('value')) die('true');
        
        $lock = Config::get('cadmin.validate.unique-lock');
        
        if (!isset($lock[Request::get('table')])) die('false');
        if (!in_array(Request::get('field'), (array) $lock[Request::get('table')])) die('false');
        
        $res = \DB::table(Request::get('table'))
                    ->where(Request::get('field'), Request::get('value'))
                    ->limit(1);
        
        if (array_key_exists(Request::get('table').'-foreign', $lock)
            && Request::get('foreign')
            && in_array(Request::get('foreign'), $lock[Request::get('table').'-foreign'])
           ) {
               $res->where(Request::get('foreign'), '=', Request::get('foreignID'));
        }
        
        if (Request::get('not')) {
            if (Request::get('table') == 'permalinks')
                $res->whereNotIn('id', Permalink::select('id')->whereModule(Request::get('module'))->whereUniqid(Request::get('not'))->pluck('id')->all());
            else
                $res->where('id', '!=', Request::get('not'));
        }
        
        if ($res->count() > 0) return 'false';
        
        return 'true';
    }
}