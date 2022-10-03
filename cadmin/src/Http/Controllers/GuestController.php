<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Hash;
use Cactuar\Admin\Models\Conf;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\EmailTemplate;
use Cactuar\Admin\Models\EmailSending;
use Cactuar\Admin\Models\User;
use Cactuar\Admin\Helpers\helper;
use Cactuar\Admin\Helpers\recaptcha;
use Validator;

class GuestController extends Controller
{
    public function __construct()
    {
		view()->share('website', Conf::initial('site-setting'));
    }

 	public function getIndex()
	{	
        if ($this->logged($redirect) == true)
            return $redirect;
        return view('cactuar::admin.login');
	}
	
	public function getLogin()
	{
        return $this->getIndex();
	}
	
	public function postLogin()
	{
        if (config('cadmin.password.login-captcha') == true && recaptcha::validateV3(request()->get('g-recaptcha-response')) != true) {
            view()->share('errc',['make sure you are not a Robot by tick Recaptcha bellow']);
            return $this->getLogin();
        }

        $redPath = Request::get('redirect', null);
        
        if ($this->logged($redirect, $redPath))   
            return $redirect;
            
        if (!Request::has('username') || !Request::has('password')) {
            return $this->getLogin();
        }
        
        if (!Auth::attempt(['name' => Request::get('username'), 'password' => Request::get('password'), 'is_enable' => 'Yes'])) {
            view()->share('errc', ['user not found']);
            return $this->getLogin();
        }
        
        $passwordDay = Auth::user()->passwordDay();
        $passwordExpiry = (int) config('cadmin.password.password-expiry');
        if ($passwordExpiry > 0 && $passwordDay > $passwordExpiry) {
            view()->share('errc',['Your password has been expired. Please reset your password by <a href="'.url()->admin('guest/forgot').'">verify your username</a>']);
            Auth::logout();
            return $this->getLogin();
        }
        
        if ($this->logged($redirect, $redPath) == true) {
            Log::write('','Login');
            return $redirect;
        }
        
        return $this->getLogin();
    }
    
    public function getLogout()
    {
        Auth::logout();
        view()->share('warningc', ['session has been cleared']);
        return $this->getIndex();
    }

    public function logged(&$redirect = null, $redPath = null)
    {
        if (Auth::check() != true)
            return false;

        $path = 'profile';
        
        $custom = config('cadmin.cadmin.after-login');
        if (!is_null($redPath)) {
            $ex1 = explode('?', $redPath);
            $ex = explode('/',$ex1[0]);
            if (!array_get($ex,1))
                $ex[1] = 'index';
            $module = $ex[0];
            $action = $ex[1];
            if (\Cactuar\Admin\Helpers\admin::moduleExists($module) && Auth::user()->allow($module,$action))
                $path = $module.'/'.$action.(array_get($ex1,1) ? "?".$ex1[1] : '' );
        } elseif ($custom) {
            $ex = explode('/',$custom);
            if (!array_get($ex,1))
                $ex[1] = 'index';
            $module = $ex[0];
            $action = $ex[1];
            if (Auth::user()->allow($module,$action))
                $path = $module.'/'.$action;
        }
        
        $redirect = redirect(url()->admin($path))->with('success', 'Welcome '.e(Auth::user()->display_name).'!');

        return true;
    }

    public function getForgot()
    {
        if ($this->logged($redirect))   
            return $redirect;
            
        return view('cactuar::admin.forgot');
    }

    public function postForgot()
    {
        if ($this->logged($redirect))
            return $redirect;
        
        if (config('cadmin.password.login-captcha') == true && recaptcha::validateV3(request()->get('g-recaptcha-response')) != true) {
            view()->share('errc',['make sure you are not a Robot by tick Recaptcha bellow']);
            return $this->getForgot();
        }
        
        $post = request()->all();
        $valid = Validator::make($post,['username'=>'string|required|exists:users,name,is_enable,yes']);
        if ($valid->fails())
            return redirect(url()->admin('guest/forgot'))->with('error',implode('<br>',$valid->errors()->all()));

        //check email template -> create if empty
        if (EmailTemplate::wherePurpose('admin-forgot-password')->count() <= 0) {
            $t = new EmailTemplate;
            $t->purpose = 'admin-forgot-password';
            $t->type = 'Admin Custom';
            $t->subject_admin = 'Reset Password';
            $t->body_admin = '<p>Please follow these <a href="[link]">link</a>  to reset your password</p>';
            $t->save();
        }

        $token = null;
        while (is_null($token) || User::whereForgotToken($token)->count() >= 1) {
            $token = Hash::make(uniqid().rand(10000,20000).'-cactuar-forgot-password');
        }

        $user = User::whereName($post['username'])->firstOrFail();
        if (!$user->email)
            return redirect(url()->admin('guest/forgot'))->with('error','invalid user');

        $user->forgot_token = $token;
        $user->forgot_token_expired = date('Y-m-d H:i:s', time() + (6 * 60 * 60));
        $user->timestamps = false;
        $user->save();

        //create log
        $log = new Log;
        $log->user_id = $user->id;
        $log->username = $user->name;
        $log->user_display_name = $user->display_name;
        $log->module = '';
        $log->act = 'request forgot password';
        $log->post = json_encode($post);
        $log->save();

        //send email template
        EmailSending::initial('admin-forgot-password')->to([$user->email])
            ->data(['link' => url()->admin('guest/reset-password').'?token='.$token])
            ->send();

        return redirect(url()->admin('guest/forgot'))->with('success','Please check your e-mail for the next step to change your password');
    }

    public function getResetPassword()
    {
        $token = request()->query('token');
        $user = User::whereIsEnable(1)->whereForgotToken($token)->where('forgot_token_expired','>=',date('Y-m-d H:i:s'))->first();
        if (!$user)
            return redirect(url()->admin('guest/forgot'))->with('error','Your token may be invalid or may have expired');

        $password = helper::randCode(10,'alnum');
        $user->password = Hash::make($password);
        $user->forgot_token = null;
        $user->forgot_token_expired = null;
        $user->timestamps = false;
        $user->save();
        $user->passwordHistory('reset');
        
        if (EmailTemplate::wherePurpose('admin-new-password')->count() <= 0) {
            $t = new EmailTemplate;
            $t->purpose = 'admin-new-password';
            $t->type = 'Admin Custom';
            $t->subject_admin = 'Your new Password';
            $t->body_admin = '<p>Your new password has been reset into <b>[password]</b> please login with your new password</p>';
            $t->save();
        }
        //create log
        $log = new Log;
        $log->user_id = $user->id;
        $log->username = $user->name;
        $log->user_display_name = $user->display_name;
        $log->module = '';
        $log->act = 'reset password';
        $log->post = json_encode([]);
        $log->save();

        //send email template
        EmailSending::initial('admin-new-password')->to([$user->email])
            ->data(['password'=>$password])
            ->send();

        return redirect(url()->admin('guest/login'))->with('success','Your password has been reset, please check your email for your new password');
    }
}