<?php  namespace App\Http\Controllers\Admin;

use	Cactuar\Admin\Models\Menu;
use Cactuar\Admin\Http\Controllers\MenuController as BaseController;
use Auth;

class MenuCustomViewController extends BaseController
{

	protected $templates = [
        'template-1' => [
            'label' => 'Template #1',
            'preview' => 'https://www.google.co.id/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
            'widgets' => [
                'banner' => [
                    'subtitle' => 'Banner',
                    'widgets' => [
                        'image' => [
                            'type' => 'text',
                        ]
                    ],
                    'max' => 1
                ]
            ]
        ],
        'template-2' => [
            'label' => 'Template #2',
            'preview' => 'https://www.google.co.id/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
        ]
    ];
    
    protected $deep = 3;
    
    public function draftAble($item)
    {
        return !in_array($item->type, ['blank','url']);
    }

    public function viewChild($parent, $deep = 0)
    {
        $out = [];
        foreach (Menu::translate()->whereParentId($parent)->orderBy('sort_id')->orderBy('id')->get() as $item) {
            $out[$item->id] = [
                'deep' => $deep,
                'label' => $item->label,
                'type' => $this->typeLabel($item->type),
                'item' => $item,
                'childs' => $this->viewChild($item->id, $deep + 1),
                'buttons' => $this->getButton($item)
            ];
            foreach ($out[$item->id]['childs'] as $k => $v) {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    public function getIndex()
    {
        if (request()->has('search'))
            return $this->parentGetIndex();

        return view('cactuar::admin.menu-index', [
            'data' => $this->viewChild(0),
            'maxDeep' => $this->deep,
            'module' => $this->module(),
        ]);
    }

    public function getButton($item)
    {
        $module = $this->module();
        $acts = [];
        $action = '';
            
        if (method_exists($this, 'getPublish') && Auth::user()->allow($module, 'publish') && (!method_exists($this, 'publishAble') || $this->publishAble($item))){
            if ($item->is_active){
                $acts['unpublish'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=0&unique='.$item->id).'" class="btn bg-yellow btn-flat need-confirm" data-confirm="Are you sure to unpublish selected item?"><i class="fa fa-toggle-on"></i> Published</a>';
            }else{
                $acts['publish'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=1&unique='.$item->id).'" class="btn bg-gray btn-flat need-confirm" data-confirm="Are you sure to publish selected item?"><i class="fa fa-toggle-off"></i> Unpublished</a>';
            }
        }
                
        if (method_exists($this, 'getEdit') && Auth::user()->allow($module, 'edit') && (!method_exists($this, 'editAble') || $this->editAble($item))) {
            $acts['edit'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/edit?unique='.$item->id).'" class="btn bg-purple btn-flat"><i class="fa fa-edit"></i> Edit</a>';
        }

        if (method_exists($this, 'getDraft') && Auth::user()->allow($module, 'draft') && (!method_exists($this, 'draftAble') || $this->draftAble($item))) {
            $acts['draft'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/draft?unique='.$item->id).'" class="btn bg-aqua btn-flat"><i class="fa fa-edit"></i> Draft</a>';
        }

        if (
            method_exists($this, 'getApproveDraft')
            && Auth::user()->allow($module, 'approve-draft')
            && (
                !method_exists($this, 'draftApproveAble') 
                || $this->draftApproveAble($item)
            )
            && (
                !method_exists($this, 'draftApproveAvail')
                || $this->draftApproveAvail($item)
            )
        ) {
            $acts['approve-draft'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/approve-draft?unique='.$item->id).'" class="btn bg-green btn-flat">
                                    <i class="fa fa-clone"></i> Approve Draft
                                </a>';
        }
        
        if (
            method_exists($this, 'getMergeDraft')
            && Auth::user()->allow($module, 'merge-draft')
            && (
                !method_exists($this, 'mergeAble') 
                || $this->mergeAble($item)
            )
            && (
                !method_exists($this, 'mergeAvail')
                || $this->mergeAvail($item)
            )
        ) {
            $acts['merge'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/merge-draft?unique='.$item->id).'" class="btn bg-green btn-flat">
                                    <i class="fa fa-clone"></i> Merge Draft
                                </a>';
        }
        
        if (method_exists($this, 'getDelete') && Auth::user()->allow($module, 'delete') && (!method_exists($this, 'deleteAble') || $this->deleteAble($item))) {
            $acts['delete'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/delete?unique='.$item->id).'" 
                            class="btn bg-red btn-flat need-confirm" 
                            data-confirm="Are you sure to delete this item?">
                        <i class="fa fa-trash-o"></i> Delete
                    </a>';
        }

        if ($acts != []) {
            $action = implode('&nbsp;', $acts);
        }

        return $action;
    }
}