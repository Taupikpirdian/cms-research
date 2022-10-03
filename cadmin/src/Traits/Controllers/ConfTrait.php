<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Config;
use DB;
use Cactuar\Admin\Models\Conf as Model;
use Cactuar\Admin\Helpers\adminForm;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\WidgetDetail;
use Cactuar\Admin\Models\Log;

trait ConfTrait
{  
    
    public function getConfig()
    {
        $module = $this->module();
			
		$fields = $this->configFields();
		$res = Model::initial($module);
        $res->id = 99;
		
        $form = adminForm::initial($module.'-conf', $fields);
		
		$append = $this->configAppend();
		
		$data = [
			'label' => $this->label('config'),
        	'menu' => $this->baseMenu($module, 'config'),
			'form' => $form->draw($res),
            'append' => $form->widgetSource() . $append,
		];
		
		return view('cactuar::admin.config', compact('data'));
    }
    
    public function postConfig()
    {	
        if (!isset($this->module))
            $module = \Cactuar\Admin\Helpers\admin::module();
        else
            $module = $this->module;
        
        $form = adminForm::initial($module, $this->configFields());
        $post = $form->postData();
        
		$post['main'] = [];                  
		
        foreach ($this->configFields() AS $key => $val) {
			$row = ['key' => $key];
			if (array_get($val, 'multilang') == true) {
				foreach (Config::get('cadmin.lang.codes') AS $lang) {
					$row['lang'] = $lang;
					$row['val'] = (string) Request::input($key.'_'.$lang);
					array_push($post['main'], $row);
				}
			} else {
				$row['lang'] = '';
				$row['val'] = (string) Request::input($key);
				array_push($post['main'], $row);
			}
		}
                          
        $post = $this->configCallbackBefore($post);
        
        \DB::transaction(function() use($module, $post) {
            Model::whereModule($module)->delete();
            
            foreach (Widget::whereUniqid(99)->wheremodule($module.'-conf')->get() AS $old) {
                WidgetDetail::whereWidgetId($old->id)->delete();
                $old->delete();
            }

            foreach (array_get($post, 'widget') AS $k => $v) {
                foreach ($v AS $kk => $vv) {
                    $w = new Widget;
                    $w->uniqid = 99;
                    $w->module = $module.'-conf';
                    $w->key = $k;
                    $w->save();

                    foreach ($vv AS $kkk => $vvv) {
                        $wd = new WidgetDetail;
                        $wd->widget_id = $w->id;
                        $wd->lang = array_get($vvv, 'lang');
                        $wd->field_name = array_get($vvv, 'field_name');
                        $wd->val = array_get($vvv, 'val') ? array_get($vvv, 'val') : '';
                        $wd->save();
                    }	
                }
            }
            
            foreach (array_get($post, 'main') AS $k => $v) {
                $conf = new Model;
                $conf->module = $module;
                $conf->key = array_get($v, 'key');
                $conf->lang = array_get($v, 'lang');
				$conf->val = array_get($v, 'val');
                $conf->save();
            }
            
            $this->configCallbackAfter();
        });
        
        Log::write($module, 'conf');
        return back()->with('success', 'Data has been saved');
        
    }
	
	public function configFields()
	{
		return [];
	}
	
	public function configAppend()
	{
		return '';
	}
	
	public function configParam()
	{
	
	}
    
    public function configCallbackBefore($post)
	{
		return $post;
	}
	
    public function configCallbackAfter()
    {
        return true;   
    }
    
    public function configMainMenu($menu)
    {
        return $menu;
    }
	
}