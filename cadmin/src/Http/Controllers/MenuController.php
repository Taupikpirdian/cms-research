<?php  namespace Cactuar\Admin\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use	Cactuar\Admin\Models\Menu;
use Cactuar\Admin\Traits\Controllers\BaseTrait;
use Cactuar\Admin\Traits\Controllers\ListingTrait;
use Cactuar\Admin\Traits\Controllers\CreateTrait;
use Cactuar\Admin\Traits\Controllers\EditTrait;
use Cactuar\Admin\Traits\Controllers\DeleteTrait;
use Cactuar\Admin\Traits\Controllers\PublishTrait;
use Cactuar\Admin\Traits\Controllers\SearchableTrait;
use Cactuar\Admin\Helpers\media;

class MenuController extends Controller
{
    use BaseTrait, ListingTrait, CreateTrait, EditTrait, DeleteTrait, PublishTrait/*, SearchableTrait*/
    {
        getCreate as parentGetCreate;   
        getIndex as parentGetIndex;
    }
	
    protected $deep = 2;
    protected $parents = [];
    protected $templates = [];
    protected $defaultType = ['blank'=>'Blank Menu','url'=>'External Link'];
    protected $positions = ['main' => 'Main', 'footer' => 'Footer'];
    
    public function __construct()
    {
        $this->child($this->parents,0,'',1);
    }
    
    public function listingRes()
    {
        return Menu::translate();
    }
    
    public function listingOrderDefault($q)
    {
        return $q->orderBy('parent_id')->orderBy('sort_id')->orderBy('id','desc');
    }
    
    public function listingFields()
    {
        $fields = ['label'=>'Label','type'=>'Type'];
        /*if ($this->deep <= 1)
            unset($fields['parent_id']);*/
        return $fields;
    }
    
    public function listingCallback($key,$value,$item,$type)
    {
        if ($key == 'label') {
            if ($item->parent_id != 0)
                return array_get($this->parents,$item->parent_id).' / <b>'.e($value).'</b>';
            else
                return '<b>'.e($value).'</b>';
        }
        if ($key == 'type')
            return $this->typeLabel($value);
    }
    
    public function listingSearchs()
    {
        return ['label'];
    }
    
    public function listingFilters()
    {
        if ($this->deep > 1)
            return ['parent_id' => ['label' => 'Parent', 'options' => $this->parents]];
        return [];
    }
    
    public function formRes()
    {
        return new Menu;
    }
    
    public function getCreate()
    {
		$templates = $types = [];
		
		foreach ($this->templates as $k=>$v) {
			if (array_get($v,'max') && Menu::whereType($k)->count() >= (double) array_get($v,'max'))
				continue;
			$templates[$k] = $v;
		}
		
        if (request()->validated('type','string|required|in:'.implode(',',array_merge(array_keys($templates),array_keys($this->defaultType)))))
			return $this->parentGetCreate();
			
		return view('cactuar::admin.menu-type-selector',[
			'module'=>$this->module(),
			'baseMenu'=>$this->baseMenu($this->module(),'create'),
            'subMenu'=>$this->subMenu($this->module(),'create'),
			'templates'=>$templates,
			'defaultType'=>$this->defaultType
		]);
    }
    
    public function typeLabel($dataType)
    {
        $dataTypeLabel = '';
        if (array_key_exists($dataType,$this->templates))
            $dataTypeLabel = 'Template : '.array_get($this->templates,$dataType.'.label');
        else
            $dataTypeLabel = array_get($this->defaultType,$dataType);
        
        return $dataTypeLabel;
    }
    
    public function formFields($type)
    {
        if ($type == 'create')
            $dataType = request()->validated('type','string|required');
        
        if (in_array($type, ['edit','draft'])) {
            $item = Menu::select('type')->findOrFail(request()->validated('unique','string|required'));
            $dataType = $item->type;
        }
        
        $parents = ['0' => '- as Parent'];
        foreach ($this->parents as $k => $v) {
            if ($type == 'edit' && request()->get('unique') == $k)
                continue;
            
            $parents[$k] = $v;
        }
        
        $fields = [
            'type_html' => [
                'type' => 'free',
                'subtitle' => $this->typeLabel($dataType),
                'html' => ($type == 'create') ? '<a href="'.url()->admin($this->module().'/create').'"><i class="fa fa-arrow-left btn btn-flat btn-xs bg-green"></i> select other type / template</a>' : ''
            ],
            'parent_id' => [
                'type' => 'select',
                'label' => 'Parent',
                'options' => $parents,
            ],
            'label' => [
                'type' => 'text',
                'label' => 'Label',
                'multilang' => true,
                'attributes' => [
                    'class' => 'required title',
                ]
            ],
            'permalink' => [
                'type' => 'permalink',
            ],
            'url' => [
                'type' => 'text',
                'label' => 'URL',
                'multilang' => true,
                'attributes' => [
                    'class' => 'required url',
                ]
            ],
			'position' => [
				'type' => 'multicheck',
				'label' => 'Position',
				'options' => $this->positions,
				'attributes' => [
					'class' => 'minimal',
					]
				],
        ];
        
        if ($this->deep <= 1)
            unset($fields['parent_id']);
        
        if ($dataType == 'url') {
            unset($fields['permalink']);
        } else {
            unset($fields['url']);
        }
        if ($dataType == 'blank') {
            unset($fields['permalink']);
            unset($fields['url']);
        }
        
        if (array_key_exists($dataType, $this->templates) && is_array(array_get($this->templates,$dataType.'.widgets'))) {
            foreach ($this->templates[$dataType]['widgets'] as $k => $v) {
                $v['type'] = 'widget';
                $fields[$k] = $v;
            }
        }
        
        if (!in_array($dataType,['url','blank']))
            $fields['meta'] = ['type' => 'meta'];
        
        return $fields;
    }
    
	public function formCallbackBefore($post,$item,$type)
	{
		if ($type == 'create')
			$post['main']['type'] = request()->get('type');
		
		if (!array_get($post['main'],'parent_id'))
			$post['main']['parent_id'] = 0;
		
		return $post;
	}
    
    public function child(&$out,$parentId,$bread,$deep)
    {
        if ($deep >= $this->deep)
           return;
        
        $deep++;
        
        if ($bread)
            $bread .= ' / ';
        
        foreach (Menu::translate()->select('id','label')->whereParentId($parentId)->orderBy('sort_id')->get() as $v) {
            $out[$v->id] = $bread.$v->label;
            self::child($out,$v->id,$bread.$v->label,$deep);
        }
    }
    
    public function deleteAble($item)
    {
        return Menu::whereParentId($item->id)->count() < 1;
    }
}