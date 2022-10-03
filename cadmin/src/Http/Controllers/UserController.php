<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
use Request;
use Cactuar\Admin\Models\User;
use Cactuar\Admin\Models\Privilege;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Traits\Controllers\BaseTrait;
use Cactuar\Admin\Traits\Controllers\EmailTemplateTrait;
use Validator;

class UserController extends Controller
{   
    use BaseTrait, EmailTemplateTrait;

    public function emailTemplates()
    {
        return [
            'admin-forgot-password' => [
                'type' => 'Admin Custom',
                'label' => 'Admin Forgot Password',
		'required' => true,
                'keywords' => [
                    'link' => 'Reset Password Link',
                ]
            ],
            'admin-new-password' => [
                'type' => 'Admin Custom',
                'label' => 'Admin Reset Password',
		'required' => true,
                'keywords' => [
                    'password' => 'New generated password',
                ]
            ]
        ];
    }

    public function getIndex()
    {
        view()->share('title', 'User');
        view()->share('sort', ['name' => 'Login Name', 'display_name' => 'Display Name', 'email' => 'Email']);
        
        if (Request::query('sort') && Request::query('sortType')) {
            $listing = User::orderBy(Request::query('sort'), Request::query('sortType'));
        } else {
            $listing = User::orderBy('display_name', 'asc');
        }
        
        $listing->whereNotNUll('privilege_id');
        
        if (Request::query('search')) {
            $listing->where(function ($query) {
                        $query->orWhere('display_name', 'like', '%'.Request::query('search').'%');
                        $query->orWhere('email', 'like', '%'.Request::query('search').'%');
                        $query->orWhere('name', 'like', '%'.Request::query('search').'%');
                        });
        }
        
        view()->share('listing', $listing->paginate(30));
        return view('cactuar::admin.user-list'); 
    }
    
    public function getCreate()
    {
        view()->share('title', 'Create New user');
        view()->share('data', new user());
        view()->share('type', 'create');
		view()->share('privilege', Privilege::orderBy('label', 'asc')->pluck('label', 'id')->all());
        
        return view('cactuar::admin.user-form');
    }
    
    public function postCreate()
    {
        if ($this->_save('create') == true)
            return admin::redirect('user')->with('success', 'Data successfuly saved');
        
        return $this->getCreate();
    }
    
    public function getEdit()
    {
        view()->share('title', 'User Detail');
        view()->share('data', User::findOrFail(Request::query('unique')));
        view()->share('type', 'detail');
		view()->share('privilege', Privilege::orderBy('label', 'asc')->pluck('label', 'id')->all());
        
        return view('cactuar::admin.user-form');
    }
    
    public function postEdit()
    {
        if ($this->_save('update', Request::query('unique')) == true)
            return admin::redirect('user/edit?unique='.Request::input('unique'))->with('success', 'Data successfuly updated.');
        
        return $this->getEdit();
    }
    
    public function getDelete()
    {
        User::findOrFail(Request::query('unique'))->delete();
        return admin::redirect('user')->with('success', 'Data successfuly deleted.');
    }
    
    private function _save($act, $unique = null)
    {
        $rules = [
            'name' => 'string|required|max:255|unique:users,name,'.$unique.'|regex:/^[a-z][-a-z0-9]*$/',
            'display_name' => 'string|required|max:255',
            'password' => 'string|required|strong-password',
            'email' => 'string|required|email',
            'privilege_id' => 'numeric|required|exists:privileges,id'
        ];
        
        if ($act == 'update')
            $rules['password'] .= '|unique-history-Password:'.$unique;
        
        if ($act != 'create' && !request()->get('password'))
            unset($rules['password']);
        
        $valid = Validator::make(request()->all(),$rules);
        if ($valid->fails()) {
            view()->share('errc',$valid->errors()->all());
            return false;
        }
        
        if ($act == 'create') {
            $user = new User();
        } else {
            $user = User::findOrFail($unique);
        }
        
        $user->name = Request::input('name');
        $user->display_name = Request::input('display_name');
        $user->email = Request::input('email');
        $user->privilege_id = Request::input('privilege_id') ? Request::input('privilege_id') : 0;
        $user->is_enable = Request::input('is_enable');
        
        if ($act == 'create' || Request::input('password')) {
            $user->password = \Hash::make(Request::input('password'));
        }
        
        $user->save();
        
        if ($act == 'create' || Request::input('password'))
            $user->passwordHistory('crud');
        
        return true;
    }
}
