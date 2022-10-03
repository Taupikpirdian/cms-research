<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Config;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Helpers\adminForm;
use Cactuar\Admin\Helpers\admin;

trait EditTrait
{
    public function getEdit()
    {
        $module = $this->module();
        $fields = $this->fields('edit');
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
		
		if (!$this->editAble($res)) 
			abort(404);
		
		$form = adminForm::initial($module, $fields);
		
		$append = $this->append('edit');
		
		$data = [
			'label' => $this->label('edit'),
        	'menu' => $this->baseMenu($module, 'edit'),
            'subMenu' => $this->subMenu($module, 'edit'), 
			'form' => $form->draw($res),
			'append' => $form->widgetSource() . $append,
		];
		
		return view('cactuar::admin.edit', compact('data'));
    }
    
    public function postEdit()
    {
		$module = $this->module();
        $fields = $this->fields('edit');
        $res = $this->res('edit');
		
		$res = $res->findOrFail(request()->validated('unique','string|numeric',true));
		if (!$this->editAble($res)) 
			abort(404);
        
		$form = adminForm::initial($module, $fields);
        
        if ($this->validateCallback($form->postData(), 'edit', $error) !== true) {
            return redirect()->back()->with('error',implode('<br>',$error));
			/*view()->share('formValidate', $error);
            return $this->getEdit();*/
        }
        
		$save = $form->save($res, 
					function($post, $item) { 
                        if (method_exists($this,'searchable'))
                            $post = $this->searchable($post,$item,'edit');
						if (method_exists($this, 'formCallbackBefore'))
							return $this->formCallbackBefore($post, $item, 'edit');
						return $this->editCallbackBefore($post, $item); 
					},
					function($item) {
						if (method_exists($this, 'formCallbackAfter'))
							return $this->formCallbackAfter($item, 'edit');
						return $this->editCallbackAfter($item, 'edit');
					});
		
		if ($save) {
			event(new \Cactuar\Admin\Events\CudAfter($module,$res,'edit'));
			
			$log = $this->log($res, 'edit');
			Log::write($module, 'edit', $res->id, $log);
			
			return redirect()->back()->with('success', 'Data has been updated');
		} else {
			return redirect()->back()->with('error','Faied update data');
			/*view()->share('warningc', ['Faied update data']);
			return $this->getEdit();*/
		}
    }
	
	public function editMainMenu($menu)
	{
		return $menu;
	}
	    
	public function editRes()
	{
		return null;
	}
	
	public function editFields()
	{
		return [];
	}
	
    public function editAble($item)
    {
        return true;
    }
	
	public function editCallbackBefore($post,$item)
	{
		return $post;
	}
	
    public function editCallbackAfter($item)
    {
		return true;
    }
	
	public function editLog($item)
	{
		return '';
	}
	
	public function editAppend()
	{
		return '';
	}
    
    public function editValidateCallback($post,&$error = [])
    {
        return true;
    }
}