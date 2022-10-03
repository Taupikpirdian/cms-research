<?php namespace Cactuar\Admin\Traits\Controllers;

use Cactuar\Admin\Helpers\adminFormDraft;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\Draft;
use Cactuar\Admin\Models\DraftLog;
use Cactuar\Admin\Models\MetaData;
use Cactuar\Admin\Models\Widget;
use Auth;

trait DraftTrait
{
    public function getDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
		
		if (!$this->draftAble($res)) 
			abort(404);
		
        if (request()->query('preview') == 1 && $this->previewAble($res))
            return $this->preview($res);
        
		$form = adminFormDraft::initial($module, $fields);
		
        $append = $this->append('draft');
		
        $label = $this->label('draft');
        
        if ($title = $this->draftTitle($res))
            $label .= ' : '.$title;
        elseif ($log = $this->log($res,'draft'))
            $label .= ' : '.$log;
        
		$data = [
			'label' => $label,
        	'menu' => $this->baseMenu($module, 'draft'),
            'subMenu' => $this->subMenu($module, 'draft'), 
			'form' => $form->draw($res),
            'log' => $this->lastDraft($res),
			'append' => $form->widgetSource() . $append,
            'deleteUrl' => Auth::user()->allow($module, 'delete-draft') && $this->deleteDraftAble($res) && $this->draftExists($res) ? url()->admin($module.'/delete-draft?unique='.$res->id) : null,
            'previewUrl' => $this->previewAble($res) && $this->draftExists($res) ? url()->admin($module.'/draft?unique='.$res->id.'&preview=1') : null,
        ];
		
		return view('cactuar::admin.draft', compact('data'));
    }
    
    public function postDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        $res = $this->res('edit');
		
		$res = $res->findOrFail(request()->validated('unique','string|numeric',true));
		if (!$this->draftAble($res)) 
			abort(404);
        
		$form = adminFormDraft::initial($module, $fields);
        
        if ($this->draftValidateCallback($form->postData(), $error) !== true) {
            view()->share('formValidate', $error);
            return $this->getDraft();
        }
        
		$save = $form->save($res,
					function($post, $item) {
                        return $this->draftCallbackBefore($post,$item); 
					},
					function($item) {
						return $this->draftCallbackAfter($item);
					},$this->log($res));
		
		if ($save) {
            
            event(new \Cactuar\Admin\Events\DraftAfter($module, $res, 'draft'));

			$log = $this->log($res, 'draft');
			Log::write($module, 'draft', $res->id, $log);
			
			return redirect()->back()->with('success', 'Data has been updated');
		} else {
			view()->share('warningc', ['Faied update data']);
			return $this->getDraft();
		}
    }
    
    public function getApproveDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
		
        if (!$this->draftApproveAble($res) || !$this->draftApproveAvail($res)) 
			abort(404);
		
        if (request()->query('preview') == 1 && $this->previewAble($res))
            return $this->preview($res);
        
        $form = adminFormDraft::initial($module, $fields);
		
		$append = $this->append('approve-draft');
		
        $label = $this->label('approve-draft');
        
        if ($title = $this->draftTitle($res))
            $label .= ' : '.$title;
        elseif ($log = $this->log($res,'draft'))
            $label .= ' : '.$log;
        
        $data = [
			'label' => $label,
        	'menu' => $this->baseMenu($module, 'draft'),
            'subMenu' => $this->subMenu($module, 'draft'), 
			'form' => $form->draw($res),
            'log' => $this->lastDraft($res),
			'append' => $form->widgetSource() . $append,
            'deleteUrl' => Auth::user()->allow($module, 'delete-draft') && $this->deleteDraftAble($res) && $this->draftExists($res) ? url()->admin($module.'/delete-draft?unique='.$res->id) : null,
            'previewUrl' => $this->previewAble($res) && $this->draftExists($res) ? url()->admin($module.'/approve-draft?unique='.$res->id.'&preview=1') : null,
        ];
		
		return view('cactuar::admin.approve-draft', compact('data'));
    }
    
    public function postApproveDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
        
		if (!$this->draftApproveAble($res) || !$this->draftApproveAvail($res)) 
			abort(404);
        
        $form = adminFormDraft::initial($module, $fields);
        
        $save = $form->approve($res, 
                    function($post,$res) {
                       return $this->draftApproveCallbackBefore($post,$res);
                    },
                    function($res) {
                        return $this->draftApproveCallbackAfter($res); 
                    });
        
        if ($save) {
            event(new \Cactuar\Admin\Events\DraftAfter($module, $res, 'approve-draft'));

			$log = $this->log($res, 'draft');
			Log::write($module, 'approve draft', $res->id, $log);
			return redirect(url()->admin($this->module()))->with('success', 'Draft has been approved');
		} else {
			view()->share('warningc', ['Faied approve data']);
			return $this->getMerge();
		}
    }
    
    public function getMergeDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
		
        if (!$this->mergeAble($res) || !$this->mergeAvail($res)) 
			abort(404);
		
        if (request()->query('preview') == 1 && $this->previewAble($res))
            return $this->preview($res);
        
        $form = adminFormDraft::initial($module, $fields);
		
		$append = $this->append('merge');
		
        $label = $this->label('merge');
        
        if ($title = $this->draftTitle($res))
            $label .= ' : '.$title;
        elseif ($log = $this->log($res,'draft'))
            $label .= ' : '.$log;
        
        $data = [
			'label' => $label,
        	'menu' => $this->baseMenu($module, 'draft'),
            'subMenu' => $this->subMenu($module, 'draft'), 
			'form' => $form->draw($res),
            'log' => $this->lastDraft($res),
			'append' => $form->widgetSource() . $append,
            'deleteUrl' => Auth::user()->allow($module, 'delete-draft') && $this->deleteDraftAble($res) && $this->draftExists($res) ? url()->admin($module.'/delete-draft?unique='.$res->id) : null,
            'previewUrl' => $this->previewAble($res) && $this->draftExists($res) ? url()->admin($module.'/merge-draft?unique='.$res->id.'&preview=1') : null,
        ];
		
		return view('cactuar::admin.merge', compact('data'));
    }
    
    public function postMergeDraft()
    {
        $module = $this->module();
        $fields = $this->fields('draft');
        
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
        
		if (!$this->mergeAble($res) || !$this->mergeAvail($res)) 
			abort(404);
        
        $form = adminFormDraft::initial($module, $fields);
        
        $merge = $form->merge($res, 
                            function($post,$item) {
                                return $this->draftMergeCallbackBefore($post,$item);   
                            },
                            function($res) { 
                                return $this->draftMergeCallbackAfter($res); 
                            });
        
        if ($merge) {
            event(new \Cactuar\Admin\Events\DraftAfter($module, $res, 'merge-draft'));

			$log = $this->log($res, 'draft');
			Log::write($module, 'merge draft', $res->id, $log);
			return redirect(url()->admin($this->module()))->with('success', 'Draft has been merged');
		} else {
			view()->share('warningc', ['Faied merge data']);
			return $this->getMerge();
		}
    }
    
    public function getDeleteDraft()
    {
        $module = $this->module();
        $res = $this->res('edit')->findOrFail(request()->validated('unique','string|numeric',true));
        $log = $this->log($res, 'delete-draft');
        
        if (!$this->draftExists($res))
            abort(404);
        
        $form = adminFormDraft::initial($module);
        
        $save = $form->deleteDraft($res, 
                                function($res) { 
                                    return $this->draftDeleteCallbackAfter($res); 
                                });
        
        if ($save) {
            event(new \Cactuar\Admin\Events\DraftAfter($module,$res,'delete-draft'));
			Log::write($module, 'delete-draft', request()->get('unique'), $log);
			
			return redirect(url()->admin($this->module()))->with('success', 'Draft has been reset');
		} else {
			view()->share('warningc', ['Faied update data']);
			return $this->getDraft();
		}
    }
    
	public function draftMainMenu($menu)
	{
		return $menu;
	}
    
    public function draftFields()
    {
        return [];
    }
    
    public function draftAble($item)
    {
        return true;
    }
    
    public function deleteDraftAble($item)
    {
        return true;
    }
    
    public function draftAppend()
    {
        return '';
    }
    
    public function draftTitle($item)
    {
        foreach (['title','label','name'] as $v)
            if ($item->{$v})
                return $item->{$v};
        return '';
    }
    
    public function draftValidateCallback($post,&$error = [])
    {
        return true;
    }
    
    public function mergeAble($item)
    {
        return $this->draftApproveAble($item);
    }
    
    public function draftApproveAble($item)
    {
        return $this->draftAble($item);
    }
    
    public function draftApproveAvail($item)
    {
        $log = $this->lastDraft($item);
        return $log && $log->status == 0;
    }
    
    public function mergeAvail($item)
    {
        $log = $this->lastDraft($item);
        return $log && $log->status >= 1;
    }
    
    private function lastDraft($item)
    {
        return DraftLog::whereModule($this->module())->whereUniqid($item->id)->first();
    }
    
    public function draftExists($item)
    {
        $log = $this->lastDraft($item);
        return $log && $log->id;
    }
    
    public function mergeAppend()
    {
        return $this->draftAppend();
    }
    
    public function approveDraftAppend()
    {
        return $this->draftAppend();
    }
    
    public function preview($item)
    {
        return '<div style="font-family:Arial;height:100vh;padding-top:30vh;"><div style="padding:30px;width:200px;border:thin solid #aaa;margin:0 auto;text-align:center;">Preview Draft</div></div>';    
    }
    
    public function previewAble($item)
    {
        return true;
    }
    
    
    public function draftCallbackBefore($post,$item)
    {
        return $post;
    }
    
    public function draftCallbackAfter($item)
    {
        return true;
    }
    
    public function draftApproveCallbackBefore($post,$item)
    {
        return $post;
    }
    
    public function draftApproveCallbackAfter($item)
    {
        return true;
    }
    
    public function draftMergeCallbackBefore($post,$item)
    {
        return $post;
    }
    
    public function draftMergeCallbackAfter($item)
    {
        return true;
    }
    
    public function draftDeleteCallbackAfter($item)
    {
        return true;
    }
}