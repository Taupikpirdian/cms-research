<?php

namespace Cactuar\Admin\Http\Middleware;

use Cactuar\Admin\Helpers\helper;
use Closure;
use Auth;
use Config;
use Request;
use Validator;

class Admin
{
    public function handle($request, Closure $next, $type = '')
    {
        $passwordInfo = [];
        
        if (config('cadmin.password.weak-password') != true)
            $passwordInfo['weak-password'] = 'password must at least 8 character and contain uppercase, lowercase, numeric & special character';
        if (config('cadmin.password.unique-password-history') > 0)
            $passwordInfo['unique-password-history'] = 'password must different from last '.config('cadmin.password.unique-password-history').' history';
        if (config('cadmin.password.password-expiry') > 0)
            $passwordInfo['password-expiry'] = 'password will expired '.config('cadmin.password.password-expiry').' day(s) after last update';
        
        view()->share('passwordInfo',$passwordInfo);
        
        Validator::extend('strongPassword',function($attr, $password) {
            if (config('cadmin.password.weak-password') == true)
                return true;
            
            if (!$password || strlen($password) < 8)
                return false;
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $specialChars = preg_match('@[^\w]@', $password);

            return $uppercase && $lowercase && $number && $specialChars;
        }, array_get($passwordInfo,'weak-password'));
        
        Validator::extend('uniqueHistoryPassword',function($attr, $password, $param) {
            $unique = (int) config('cadmin.password.unique-password-history');
            if ($unique <= 0)
                return true;
            
            foreach(\Cactuar\Admin\Models\User::findOrFail(array_get($param,0))->passwordHistories()->orderBy('id','desc')->limit($unique)->get() as $v) {
                if (\Hash::check($password,$v->password) == true)
                    return false;
            }
            
            return true;
        }, array_get($passwordInfo, 'unique-password-history'));
        
		if (Auth::check()) {
            if (Auth::user()->is_enable != 'Yes')
                return \Cactuar\Admin\Helpers\admin::redirect('guest/logout');
            
            $module = \Cactuar\Admin\Helpers\admin::module();
            $act = \Cactuar\Admin\Helpers\admin::action();
            
            if ($type == 'root' && Auth()->user()->isRoot !== true)
                abort(403);
            
            if ($type == 'permission')
                Auth::user()->allowOrDie(strtolower($module), strtolower($act));
            
            $this->_menu($request);
			
            $icon = Config::get('cadmin.menu.'.$module.'.fa');
            if (!$icon)
                $icon = 'list';
            $baseTitle = '<i class="fa fa-'.$icon.'"></i> '.Config::get('cadmin.menu.'.$module.'.label');
            if (config('cadmin.menu.'.$module.'.parent')) {
                $parent = config('cadmin.menu.'.config('cadmin.menu.'.$module.'.parent'));
                if ($parent)
                    $baseTitle = '<sup style="color:#aaa;font-weight:100;">'.array_get($parent, 'label').' > </sup> '.$baseTitle.'';
            }
            view()->share('baseTitle', $baseTitle);
			view()->share('website', \Cactuar\Admin\Models\Conf::initial('site-setting'));
            
            define('inAdmin', 'yes i am');
            
            if (config('cadmin.password.password-expiry-notify') > 0) {
                $passwordDay = Auth::user()->passwordDay();
                if ($passwordDay >= (int) config('cadmin.password.password-expiry-notify')) {
                    $passwordDayRemain = (int) config('cadmin.password.password-expiry') - $passwordDay;
                    view()->share('adminWarning','Your password will expire in '.$passwordDayRemain.' day(s). please <a href="'.url()->admin('profile/chpass').'">change your password</a> immediately before it expires');
                }
            }
            
            return $next($request);
        }
        
        $module = \Cactuar\Admin\Helpers\admin::module();
        $act = \Cactuar\Admin\Helpers\admin::action();
        $query = request()->query();
        if ($module && $act && \Cactuar\Admin\Helpers\admin::moduleExists($module)) {
            $redirect = $module."/".$act;
            $countQ = count($query);
            if ($countQ) {
                $redirect .= "?";
                foreach ($query as $key => $val) {
                    $redirect .= $key . "=" . $val;
                    if ($countQ > 1)
                        $redirect .= "&";

                    $countQ--;
                }
            }

            session()->flash('redirectParam', $redirect);
        }
        
		return \Cactuar\Admin\Helpers\admin::redirect('guest/login');
    }
    
    private function _menu($request)
    {
        $conf = [];//config('cadmin.menu');
        
        //auto inject menu
        $root = config('cadmin.menu-root');
        if (Auth::user()->isRoot && !empty($root)) {
            $conf['root'] = ['label' => 'Superadmin','fa' => 'user','parent' => null,'child' => []];
            foreach ($root as $k => $v) {
                $v['parent'] = 'root';
                $conf[$k] = $v;
            }
        }
        
        foreach(config('cadmin.menu') as $k=>$v)
            $conf[$k] = $v;

        $menu = [];

        //get parent
        foreach ($conf AS $k => $v) {
            if (array_get($v, 'parent') == null)
                $menu[$k] = $v;
        }

        //get child
        foreach ($conf AS $k => $v) {
            if (is_array(array_get($v, 'routes'))) {
                $v['permission'] = [];
                foreach ($v['routes'] as $vv) {
                    if (!is_array($vv))
                        break;
                    foreach ($vv as $vvv) 
                        if (!in_array($vvv, $v['permission']))
                            $v['permission'][] = $vvv;
                }
            }
            
            $parentID = array_get($v, 'parent');
            if (!$parentID || !array_key_exists($parentID, $menu)) {
                $menu[$k] = $v;
                continue;
            }
            
            if (!array_get($menu[$parentID], 'child'))
                $menu[$parentID]['child'] = [];
        
            $menu[$parentID]['child'][$k] = $v;    
        }
        
        $module = $menu;
        if (array_key_exists('root', $module))
            unset($module['root']);
        
        //check forbidden
        foreach ($menu AS $k => $v) {
            $forbidden = true;
            $info = 0;
            
            if (is_array(array_get($v, 'child'))) {
                foreach (array_get($v, 'child') AS $kk => $vv) {
                    if (Auth::user()->allow($kk, $this->menuAction($vv)))
                        $forbidden = false;
                    else
                        unset($menu[$k]['child'][$kk]);
                }
            } else {
                if (Auth::user()->allow($k, $this->menuAction($v)))
                    $forbidden = false;
            }
            
            if ($forbidden == true)
                unset($menu[$k]);
        }
        
        //get counter
        foreach ($menu as $k => $v) {
            $counter = $this->menuCounter($v);
            if (is_array(array_get($v, 'child'))) {
                foreach (array_get($v, 'child') as $kk => $vv) {
                    $scounter = $this->menuCounter($vv);
                    $menu[$k]['child'][$kk]['counter'] = $scounter;
                    $counter += $scounter;
                    
                    $menu[$k]['child'][$kk]['counter-label'] = (string) $scounter;
                    if ($scounter > 99)
                        $menu[$k]['child'][$kk]['counter-label'] = '99+';
                }
            }
            
            $menu[$k]['counter'] = $counter;
            $menu[$k]['counter-label'] = (string) $counter;
            if ($counter > 99)
                $menu[$k]['counter-label'] = '99+';
        }
        
        view()->share(compact('menu','module'));
    }
    
    private function menuCounter($obj)
    {
        if (!array_get($obj, 'counter'))
            return 0;
        
        $counter = array_get($obj, 'counter');
        
        if (is_callable($counter))
            return (int) $counter();
            
        return (int) $counter;
    }
    
    private function menuAction($obj)
    {
        if (!array_get($obj, 'default-action'))
            return 'index';
        
        return array_get($obj, 'default-action');
    }
}
