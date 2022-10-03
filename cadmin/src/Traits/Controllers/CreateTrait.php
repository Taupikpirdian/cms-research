<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Config;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Helpers\adminForm;
use Cactuar\Admin\Helpers\admin;

trait CreateTrait
{
    
    public function getCreate()
    {
		$module = $this->module();
        $fields = $this->fields('create');
        $res = $this->res('create');
       
		$form = adminForm::initial($module, $fields);
		
		$data = [
			'label' => $this->label('create'),
        	'menu' => $this->baseMenu($module, 'create'),
            'subMenu' => $this->subMenu($module, 'create'), 
			'form' => $form->draw($res),
			'append' => $form->widgetSource() . $append = $this->append('create'),
		];
		
		return view('cactuar::admin.create', compact('data'));
    }
    
    public function postCreate()
    {
		$module = $this->module();
        $fields = $this->fields('create');
        $res = $this->res('create');
        $form = adminForm::initial($module, $fields);
        
        if ($this->validateCallback($form->postData(), 'create', $error) !== true) {
            return redirect()->back()->with('error',implode('<br>',$error));
			/*view()->share('formValidate', $error);
            return $this->getCreate();*/
        }
            
        $save = $form->save($res, 
					function($post, $item) { 
                        if (method_exists($this,'searchable'))
                            $post = $this->searchable($post,$item,'create');
						if (method_exists($this, 'formCallbackBefore'))
							return $this->formCallbackBefore($post, $item, 'create');
						return $this->createCallbackBefore($post, $item); 
					},
					function($item) {
						if (method_exists($this, 'formCallbackAfter'))
							return $this->formCallbackAfter($item, 'create');
						return $this->createCallbackAfter($item, 'create');
					});
					
		if ($save) {
			event(new \Cactuar\Admin\Events\CudAfter($module,$res,'create'));
			
            //for drafter only (yang tidak bisa publish) -> auto create draft
            if (method_exists($this,'getDraft') 
                && \Schema::hasTable('draft_logs') 
                && \Schema::hasTable('drafts') 
                && method_exists($this,'getPublish')
                && \Auth::user()->allow($module, 'publish') != true) {
                \Cactuar\Admin\Helpers\adminFormDraft::initial($module,$fields)->save($res,null,null,$this->log($res));
            }
            
			$log = $this->log($res, 'create');
			Log::write($module, 'create', $res->id, $log);
			
			return redirect()->back()->with('success', 'Data has been created');
		} else {
            return redirect()->back()->with('error','failed create data');
			/*view()->share('warningc', ['Faied create data']);
			return $this->getCreate();*/
		}
    }
	
	public function createMainMenu($menu)
	{
		return $menu;
	}
	
	public function createParam()
	{
		return [];
	}
	
	public function createRes()
	{
		return null;
	}
	
	public function createCallbackBefore($post,$item)
	{
		return $post;
	}
	
    public function createCallbackAfter($item)
    {
        return true;
    }
	
	public function createFields()
	{
		return [];
	}
	
	public function createLog($item)
	{
		return '';
	}
	
	public function createAppend()
	{
		return '';
	}
    
    public function createValidateCallback($post,&$error = [])
    {
        return true;
    }
}