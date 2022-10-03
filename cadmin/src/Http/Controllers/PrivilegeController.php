<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Models\Privilege;
use Cactuar\Admin\Models\ModulePrivilege;
use Cactuar\Admin\Helpers\admin;

class PrivilegeController extends Controller
{   
    public function getIndex()
    {
        view()->share('title', 'Privilege');        
        view()->share('sort', ['label' => 'Label']);
        
        if (Request::query('sort') && Request::query('sortType')) {
            $listing = Privilege::orderBy(Request::query('sort'), Request::query('sortType'));
        } else {
            $listing = Privilege::orderBy('label', 'asc');
        }
        
        if (Request::query('search')) {
            $listing->where(function ($query) {
                        $query->orWhere('label', 'like', '%'.Request::query('search').'%');
                    });
        }
        
        view()->share('listing', $listing->paginate(30));
        return view('cactuar::admin.privilege-list'); 
    }
    
    public function getCreate()
    {
        view()->share('title', 'Create New privilege');
        view()->share('data', new Privilege());
        view()->share('type', 'create');
        return view('cactuar::admin.privilege-form');
    }
    
    public function postCreate()
    {
        if ($this->_save('create') == true)
            return admin::redirect('privilege')->with('success', 'Data successfuly saved');
        
        return $this->getCreate();
    }
    
    public function getEdit()
    {
        view()->share('title', 'Privilege Detail');
        view()->share('data', Privilege::findOrFail(Request::query('unique')));
        view()->share('type', 'detail');
        return view('cactuar::admin.privilege-form');
    }
    
    public function postEdit()
    {
        if ($this->_save('update', Request::query('unique')) == true)
            return admin::redirect('privilege/edit?unique='.Request::input('unique'))->with('success', 'Data successfuly updated.');
        
        return $this->getEdit();
    }
    
    public function getDelete()
    {
        $privilege = Privilege::findOrFail(Request::query('unique'));
        $privilege->delete();
        
        return admin::redirect('privilege')->with('success', 'Data successfuly deleted');
    }
    
    private function _save($act, $unique = null)
    {
        if ($act == 'create') {
            $privilege = new Privilege();
        } else {
            $privilege = Privilege::findOrFail($unique);
            ModulePrivilege::wherePrivilegeId($unique)->delete();
        }
        
        $privilege->label = Request::input('label');
        $privilege->save();
        
        if ($act == 'create') 
            $unique = $privilege->id;
        
        if (is_array(Request::input('act'))) {
            foreach (Request::input('act') AS $act) {
                $ex = explode(':', $act);
                if (count($ex) != 2) continue;

                $module = new ModulePrivilege();
                $module->privilege_id = $unique;
                $module->module = $ex[0];
                $module->act = $ex[1];
                $module->save();
            }
        }
        
        return true;
    }
}