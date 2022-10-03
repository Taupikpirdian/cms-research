<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AdminController extends Controller
{
    public function postFilter()
    {
        $get = Request::query();
        
        $sort = explode('-', Request::input('sort'));
        
        $get['sort'] = $get['sortType'] = '';
        if (is_array($sort) && count($sort) == 2) {
            $get['sort'] = $sort[0];
            $get['sortType'] = $sort[1];
        }
		
		$get['search'] = Request::input('search');
        $get['range'] = Request::input('range');
        $get['filter'] = Request::input('filter');
        $get['page'] = 1;
        
        return redirect(implode('/', (array) json_decode(Request::input('segments'))).'?'.http_build_query($get));
    }
}