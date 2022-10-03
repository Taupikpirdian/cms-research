<?php namespace Cactuar\Admin\Traits\Controllers;

use Auth;
use Request;
use Config;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Helpers\helper;

trait BaseTrait
{
    protected function module()
    {
        return admin::module();
    }
    
    protected function used($trait) 
    {
        return (in_array($trait, class_uses($this)) || in_array($trait, class_uses(parent::class)));
    }
    
    protected function fields($type = 'edit')
    {
        $fields = $this->{$type.'Fields'}();
        if (method_exists($this, 'formFields'))
            $fields = $this->formFields($type);
        
        if (in_array($type,['create','edit','draft'])) {
            $newFields = [];
            foreach ($fields as $k => $v) {
                if (array_get($v, 'type') == 'permalink') { //pindahkan ke base trait
                    $newFields[$k] = [
                        'label' => 'Permalink',
                        'multilang' => true,
                        'type' => 'permalink',
                        'attributes' => [
                            'class' => 'required permalink unique uniqueAll',
                            'data-unique-by' => '.permalink',
                            'data-table' => 'permalinks',
                            'data-field' => 'permalink',
                            'data-module' => $this->module(),
                            'data-not' => (int) request()->query('unique'),
                        ]
                    ];
                    continue;
                }

                if (array_get($v, 'type') == 'meta') { //pindahkan ke base trait
                    unset($fields[$k]);
                    $newFields['meta_title'] = [
                        'subtitle' => 'Meta Data',
                        'label' => 'Meta Title',
                        'multilang' => true,
                        'type' => 'text',
                        'meta' => 'title',
                        'draft' => true,
                        'attributes' => [
                            'class' => 'required',
                            'maxlength' => 60
                        ],
                        'info' => 'maximum 60 character'
                    ];
                    $newFields['meta_keywords'] = [
                        'label' => 'Meta Keywords',
                        'multilang' => true,
                        'type' => 'textarea',
                        'meta' => 'keywords',
                        'draft' => true,
                        'attributes' => [
                            'rows' => 3,
                        ]
                    ];
                    $newFields['meta_Description'] = [
                        'label' => 'Meta Description',
                        'multilang' => true,
                        'type' => 'textarea',
                        'meta' => 'description',
                        'draft' => true,
                        'attributes' => [
                            'rows' => 3,
                            'maxlength' => 160
                        ],
                        'info' => 'maximum 160 character',
                    ];
                    $newFields['meta_image'] = [
                        'label' => 'Meta Image',
                        //'multilang' => true, //non-multilingual meta image
                        'type' => 'text',
                        'meta' => 'image',
                        'draft' => true,
                        'attributes' => [
                            'class' => 'cfind',
                            'cfind-type' => 'image',
                        ]
                    ];
                    continue;
                }

                $newFields[$k] = $v;
            }
            $fields = $newFields;
        }
        
        foreach ($fields as $k=>$v) {
            if (array_get($v,'draft') == true && $type == 'edit' && method_exists($this,'getDraft'))
                $fields[$k]['readonly'] = true;
            if (array_get($v,'draft') != true && $type == 'draft')
                unset($fields[$k]);
        }
        
        if (in_array($type,['create','edit'])) {
            if (method_exists($this->res('create'), 'reorder')
                || method_exists($this->res('edit'), 'reorder')
               ) {
                $fields['sort_id'] = [
                        'type' => 'text',
                        'label' => 'Sort Id',
                        'attributes' => [
                            'class' => 'numeric',
                            ]
                        ];   
            }

            if (method_exists($this, 'getPublish') && Auth::user()->allow($this->module(),'publish')) {
                $fields['is_active'] = [
                        'label' => 'Publish',
                        'type' => 'select',
                        'options' => [0 => 'not publish', 1 => 'publish'],
                        'attributes' => [
                            'class' => 'required',
                            'style' => 'max-width:200px;',
                            ]
                        ];
            }
        }
        
        return $fields;
    }
    
    protected function mainMenu($type, $menu)
    {
        $menus = $this->{helper::dash2camel($type).'MainMenu'}($menu);
        if (in_array($type, ['edit', 'create']) && method_exists($this, 'formMainMenu'))
            $menus = $this->formMainMenu($menu, $type);
        return $menus;
    }
    
    protected function append($type = 'edit')
    {
        $append = $this->{helper::dash2camel($type).'Append'}();
        if (in_array($type, ['edit', 'create']) && method_exists($this, 'formAppend'))
            $append = $this->formAppend($type);
        return $append;
    }
    
    protected function res($type = 'edit')
    {
        $res = null;
        if (method_exists($this, $type.'Res'))
            $res = $this->{$type.'Res'}();
        if (in_array($type, ['edit', 'create', 'delete', 'publish', 'draft', 'delete-draft', 'merge']) && method_exists($this, 'formRes'))
            $res = $this->formRes($type);
        return $res;
    }
    
    protected function validateCallback($post, $type = 'edit', &$error = [])
    {
        $out = true;
        if (method_exists($this, $type.'ValidateCallback'))
            $out = $this->{$type.'ValidateCallback'}($post, $error);
        if (in_array($type, ['edit', 'create']) && method_exists($this, 'formValidateCallback'))
            $out = $this->formValidateCallback($post, $type, $error);
        return $out;
    }
    
    protected function log($item, $type = 'edit')
    {
        $log = '';
        if (method_exists($this, $type.'Log'))
            $log = $this->{$type.'Log'}($item);
        if (!$log && method_exists($this, 'formLog'))
            $log = $this->formLog($item, $type);
        
        if (method_exists($item, 'scopeTranslate') === true) {
            $item = self::res($type)->translate()->find($item->id);
        }
        
        foreach (['title', 'label', 'name'] AS $v) {
            if (!$log)
                $log = $item->{$v};
        }
        
        return $log;
    }
    
    protected function label($type = 'edit')
    {
        if (isset($this->{$type.'Label'}) && $this->{$type.'Label'})
            return $this->{$type.'Label'};
        
        $label = Config::get('cadmin.menu.'.$this->module().'.label');
        
        if ($type == 'edit')
            return 'Edit '.$label;
        if ($type == 'create')
            return 'Create '.$label;
        if ($type == 'listing')
            return $label.' Listing';
        if ($type == 'draft')
            return $label.' Draft';
        if ($type == 'merge')
            return $label.' Merge Draft';
        return $label;
    }
    
    protected function baseMenu($module, $type)
    {
        $menu = [];
        /*if ($type != 'create'
            && method_exists($this, 'getCreate')
			&& Auth::user()->allow($module, 'create')) {
			array_push($menu, ['fa' => 'plus', 'label' => 'Create', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/create')]);
		}*/
        
        if (!in_array($type,['listing','create','edit','draft'])
            && method_exists($this, 'getIndex')
            && Auth::user()->allow($module, 'index')) {
            array_push($menu, ['fa' => 'list', 'label' => 'Index', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/index')]);
        }
			
		/*if ($type == 'listing'
            && method_exists($this, 'getDownload')
			&& Auth::user()->allow($module, 'download')
            //&& $data != []
            ) {
			array_push($menu, ['fa' => 'download', 'label' => 'Download', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/download?'.http_build_query(request()->query()))]);
		}*/
        
        if ($type != 'import'
            && method_exists($this, 'getImport')
            && Auth::user()->allow($module, 'import')) {
            array_push($menu, ['fa' => 'upload', 'label' => 'Import', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/import')]);
        }
		
		if ($type != 'config'
            && method_exists($this, 'getConfig')
			&& Auth::user()->allow($module, 'config')) {
			array_push($menu, ['fa' => 'cog', 'label' => 'Config', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/config')]); 
		}
        
        if ($type != 'email-template'
            && method_exists($this, 'getEmailTemplate')
            && Auth::user()->allow($module, 'email-template')) {
            array_push($menu, ['fa' => 'envelope', 'label' => 'Email Template', 'url' => \Cactuar\Admin\Helpers\admin::url($module.'/email-template')]);
        }
        
        return $this->mainMenu($type, $menu);
    }
    
    protected function subMenu($module,$type)
    {
        $menu = [];
        
        if (in_array($type,['create','edit','draft'])
            && method_exists($this,'getIndex')
            && Auth::user()->allow($module,'index')) {
            array_push($menu, ['fa' => 'list', 'label' => 'Back to Index', 'url' => url()->admin($module)]);
        }
        
        if ($type == 'edit'
            && method_exists($this,'getCreate')
            && Auth::user()->allow($module,'create')) {
            array_push($menu, ['fa' => 'plus', 'label' => 'Create', 'url' => url()->admin($module.'/create')]);
        }
        
        if ($type == 'listing'
           && method_exists($this,'getCreate')
           && Auth::user()->allow($module,'create')) {
            array_push($menu, ['fa' => 'plus', 'label' => 'Create', 'url' => url()->admin($module.'/create')]);
        }
        
        if ($type == 'listing'
           && method_exists($this,'getDownload')
           && Auth::user()->allow($module,'download')) {
            array_push($menu, ['fa' => 'download', 'label' => 'Download', 'url' => url()->admin($module.'/download').'?'.http_build_query(request()->query())]);
        }
        
        return $menu;
    }
}