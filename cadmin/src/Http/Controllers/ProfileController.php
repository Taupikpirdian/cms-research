<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Models\User;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Helpers\admin;
use Validator;

class ProfileController extends Controller
{
    public function __construct() { }
    
    public function getIndex()
    {
        view()->share('title', 'Profile');
        return view('cactuar::admin.profile'); 
    }
    
    public function postIndex()
    {
        $user = Auth::user();
        $user->display_name = Request::input('display_name');
        $user->email = Request::input('email');
        $user->save();
        
        Log::write('profile', 'update');
        
        return admin::redirect('profile')->with('success', 'Profile successfuly updated');
    }
    
    public function getChpass()
    {
        view()->share('title', 'Change Password');
        return view('cactuar::admin.profile-chpass');
    }
    
    public function postChpass()
    {
        $rules = [
            'old_pass' => 'string|required',
            'password' => 'string|required',
            'confirm_password' => 'string|required|same:password|strong-password|unique-history-password:'.\Auth::user()->id
        ];
        
        $valid = Validator::make(request()->all(),$rules);
        if ($valid->fails()) {
            view()->share('errc',$valid->errors()->all());
            return $this->getChpass();
        }
        
        if (!Auth::attempt(['name' => Auth::user()->name, 'password' => Request::input('old_pass')])) {
            view()->share('errc', ['Old password not match']);
            return $this->getChpass();
        }
        
        $user = Auth::user();
        $user->password = \Hash::make(Request::input('password'));
        $user->save();
        $user->passwordHistory('profile');
        
        Log::write('profile', 'change password');
        
        return admin::redirect('profile/chpass')->with('success', 'Your password sucessfuly update');
    }
}