<?php

namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Cactuar\Admin\Helpers\lang;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\Translation;

class TranslationController extends Controller
{
    protected $codes = [];
    protected $active = '';
    
    public function __construct()
    {
        \App::setLocale(lang::active());
        $lang = trans('site');
        if (!is_array($lang))
            $lang = [];
        
        $codes = [];
        foreach ($lang as $k => $v) {
            self::keys($k, $v, $codes);
        }
        $this->codes = $codes;
        
        view()->share('translateCodes', lang::codes());
        view()->share('translateKeys', $codes);
    }
    
    private static function keys($k, $v, &$codes, $parent = '')
    {
        if (!is_array($v))
            return array_push($codes, $parent.$k);

        foreach ($v as $kk => $vv) 
            self::keys($kk, $vv, $codes, $parent.$k.'.');
    }
    
    public function getIndex()
    {
        return view('cactuar::admin.translation-index');
    }
    
    public function getEdit()
    {
        request()->validated('code','string',true);
        
        if (!is_string(request()->get('code')) || !request()->get('code') || !in_array(request()->get('code'), $this->codes))
            abort(404);
        
        $code = request()->get('code');
        
        $keywords = [];
        /*$base = preg_replace("/[^0-9a-zA-Z :]/","", trans('site.'.$code));
        
        $ex = explode(' ', $base);
        foreach ($ex as $v) {
            if (substr($v, 0, 1) == ':') 
                array_push($keywords, $v);
        }*/
		$ex = str_split(trans('site.'.$code));
		$keyword = '';
		foreach ($ex as $char) {
			if ((!ctype_alpha($char) || $char == ' ') && strlen($keyword)>=2 && !in_array($char,['-','_'])) {
				$keywords[] = $keyword;
				$keyword = '';
				continue;
			}
			if ($char == ':') {
				$keyword = ':';
				continue;
			}
			if (strlen($keyword) >= 1) {
				$keyword .= $char;
				continue;
			}
		}
		if (strlen($keyword)>=2)
			$keywords[] = $keyword;
        foreach($keywords as $k=>$v) {
            if (substr($v,0,2) == ': ' || $v == ':')
                unset($keywords[$k]);
        }
        
        return view('cactuar::admin.translation-edit')->with(['param' => ['code' => $code, 'keywords' => $keywords]]);
    }
    
    public function postEdit()
    {
        if (!is_string(request()->get('code')) || !request()->get('code') || !in_array(request()->get('code'), $this->codes))
            abort(404);
        
        if (!request()->get('translation') || !is_array(request()->get('translation')))
            abort(404);
        
        $code = request()->get('code');
        $active = $this->active;
        $translation = request()->get('translation');
        
        \DB::transaction(function() use($code, $translation) {
            
			foreach ($translation as $k => $v) {
				Translation::whereCode(request()->get('code'))->whereLang($k)->delete();
            
				$trans = new Translation;
				$trans->code = $code;
				$trans->lang = $k;
				$trans->translation = $v;
				$trans->save();
			}
        });
        
        Log::write(admin::module(), 'edit', null, $code);
        return redirect(admin::url('translation/edit?code='.$code))->with('success', 'Your data has been updated');
    }
}