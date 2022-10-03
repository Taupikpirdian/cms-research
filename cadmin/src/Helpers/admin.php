<?php namespace Cactuar\Admin\Helpers;

class admin
{
    public static function url($target = '')
    {
        if (!ADMIN)
            return url($target);
        return url(ADMIN.'/'.trim($target, '/'));
    }
    
    public static function redirect($target = '')
    {
        if (!ADMIN)
            return redirect($target);
        return redirect(ADMIN.'/'.trim($target, '/'));
    }
    
    public static function module()
    {
        if (!ADMIN)
            return \Request::segment(1);
        return \Request::segment(count(explode('/', trim(ADMIN, '/'))) + 1);
    }
    
    public static function moduleExists($module)
    {
        $conf = config('cadmin.menu');
        if (array_key_exists($module, $conf) && array_get($conf, $module.'.controller'))
            return true;
        
        $conf = config('cadmin.menu-root');
        if (array_key_exists($module, $conf) && array_get($conf, $module.'.controller'))
            return true;
        
        return false;
    }
    
    public static function inAdmin()
    {
        if (!defined('inAdmin'))
            return false;
        
        return inAdmin === 'yes i am';
    }
    
    public static function action()
    {
        if (ADMIN)
            $act = \Request::segment(count(explode('/', trim(ADMIN, '/'))) + 2);
        else
            $act = \Request::segment(2);
        
        if (!$act)
            return 'index';
        return $act;
    }
    
    public static function availByDomain()
    {
        $domains = config('cadmin.cadmin.admin-domain');
        if (!$domains)
            return true;
        
        $domains = explode(',', str_replace(' ', '', trim($domains, ',')));
        return in_array(array_get($_SERVER, 'HTTP_HOST'), $domains);
    }
    
    public static function availByIP()
    {
        $ips = config('cadmin.cadmin.admin-ip');
        if (!$ips)
            return true;
        
        $ip = \Request::ip();
        $ips = explode(',', str_replace(' ', '', trim($ips, ',')));
        
        foreach ($ips as $range) {
            if (helper::ipInRange($ip, $range))
                return true;
        }
        
        return false;
    }
    
    public static function moduleRoutes(array $r, array $appendGet = [], array $appendPost = [])
    {
        $post = $get = [];
        foreach ($r as $v) {
            switch($v) {
                case 'index':
                    $get[] = 'index';
                    $get[] = 'order-down';
                    $get[] = 'order-up';
                    $post[] = 'index';
                    break;
                case 'create':
                    $get[] = 'create';
                    $post[] = 'create';
                    break;
                case 'edit':
                    $get[] = 'edit';
                    $post[] = 'edit';
                    break;
                case 'delete':
                    $get[] = 'delete';
                    break;
                case 'read':
                    $get[] = 'read';
                    break;
                case 'config':
                    $post[] = 'config';
                    $get[] = 'config';
                    break;
                case 'download':
                    $get[] = 'download';
                    break;
                case 'import':
                    $get[] = 'import';
                    $post[] = 'import';
                    break;
                case 'email-template':
                    $get[] = 'email-template';
                    $get[] = 'send-test-email';
                    $post[] = 'email-template';
                    break;
                case 'publish':
                    $get[] = 'publish';
                    break;
                case 'draft':
                    $get[] = 'draft';
                    $get[] = 'delete-draft';
                    $post[] = 'draft';
                    $get[] = 'approve-draft';
                    $post[] = 'approve-draft';
                    $get[] = 'merge-draft';
                    $post[] = 'merge-draft';
                    break;
            }
        }
        
        foreach ($appendPost as $v)
            $post[] = $v;
        foreach ($appendGet as $v)
            $get[] = $v;
        
        return ['post' => $post, 'get' => $get];
    }
    
    public static function str2permalink($str)
    {
        return trim(preg_replace('/[^A-Za-z0-9-]+/', '/', strtolower($str)), '/');
    }
}