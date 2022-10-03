<?php namespace Cactuar\Admin\Helpers;

use Config;
use DB;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Helpers\media;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\WidgetDetail;
use Cactuar\Admin\Models\Related;
use Cactuar\Admin\Models\Permalink;
use Cactuar\Admin\Models\MetaData;
use Cactuar\Admin\Models\Draft;
use Cactuar\Admin\Models\DraftLog;
use Form;
use Request;
use Schema;

class adminForm
{
	public $fields = [];
    public $widgets = [];
    public $res = null;
	public $module = '';
	
    public function __construct($module, $fields = [])
    {
        $this->module = $module;
        $this->fields = $fields;
    }
    
    public static function initial($module, $fields = [])
    {
        return new adminForm($module, $fields);
    }
    
    public function draw($res)
    {
		$this->res = $res;
        
		$form = '';
		foreach ($this->fields AS $key => $field) {
			$form .= $this->row($key, $field, $res);
		}
		
		return view('cactuar::admin.form', compact('form'));
    }
    
    public function postData()
    {
        $main = [];
		$il8n = [];
		$widget = [];
		$related = [];
		$permalink = [];
        $relation = [];
        $meta = [];
        
		foreach ($this->fields AS $key => $field) {
            if (array_get($field, 'readonly') == true)
                continue;
			if (array_get($field, 'type') == 'free')
				continue;
            
            if (array_get($field, 'type') == 'widget') {
                $this->widgets[$key] = $field;
                continue;
            }
            
            if (array_get($field, 'type') == 'permalink') {
                foreach (Config::get('cadmin.lang.codes') as $lang) {
                    $permalink[$lang] = admin::str2permalink(request()->get($key.'_'.$lang));
                }
                continue;
            }
				
			if (in_array(array_get($field, 'type'), ['multicheck','multiselect'])) {
				if (!is_array(Request::input($key))) continue;
				if (!array_get($related, $key))
					$related[$key] = [];
				foreach (Request::input($key) AS $k => $v) {
					array_push($related[$key], $v);
				}				
				continue;
			}
			
            if (in_array(array_get($field, 'type'), ['multiselect-table','multicheck-table'])) {
                $relation[$field['table']] = [
                    'table' => $field['table'],
                    'id_column' => $field['id_column'],
                    'relation_column' => $field['relation_column'],
                    'data' => is_array(request()->get($key)) ? request()->get($key) : []
                ];
                continue;
            }
			
			if (array_get($field, 'multilang') == true) {
				foreach (Config::get('cadmin.lang.codes') AS $lang) {
                    if (array_get($field, 'meta') && in_array(array_get($field, 'meta'), ['title','keywords','description','image'])) {
                        $meta[$lang][$field['meta']] = Request::input($key.'_'.$lang);
                        continue;
                    }
					if (!array_key_exists($lang, $il8n))
						$il8n[$lang] = [];
					
					$il8n[$lang][$key] = Request::input($key.'_'.$lang);
				}
				continue;
			}
            
            if (array_get($field,'meta') == 'image') {
                foreach(config('cadmin.lang.codes') as $lang) {
                    $meta[$lang]['image'] = Request::input($key);
                }
                continue;
            }
            
			$main[$key] = Request::input($key);
		}
		
		foreach ($this->widgets AS $widgetId => $widgetOpt) {
			$widget[$widgetId] = [];
            
            if (!is_array(request()->get('widget_'.$widgetId)))
                continue;
            
            foreach(request()->get('widget_'.$widgetId) as $inputId=>$inputVal) {
                $widgetRow = [];
                
                foreach ($widgetOpt['widgets'] as $widgetFieldId=>$widgetFields) {
                    if ($widgetFields['type'] == 'widget') {
                        if (!is_array(request()->get('widget_'.$widgetFieldId)))
                            continue;
                        
                        foreach (request()->get('widget_'.$widgetFieldId) as $subInputId=>$subInputVal) {
                            if ($subInputVal != $inputVal)
                                continue;
                            
                            $subWidget = [
                                'key' => $widgetFieldId,
                                'type' => 'widget',
                                'widgets' => []
                            ];
                            
                            foreach ($widgetFields['widgets'] as $subWidgetId=>$subWidgetFields) {
                                if (array_get($subWidgetFields,'multilang') == true) {
                                    foreach(lang::codes() as $lang) {
                                        array_push($subWidget['widgets'],[
                                            'lang' => $lang,
                                            'field_name' => $subWidgetId,
                                            'val' => Request::input('widget_'.$widgetFieldId.'_'.$subWidgetId.'_'.$lang.'.'.$subInputId)
                                        ]);
                                    }
                                    continue;
                                }
                                
                                array_push($subWidget['widgets'],[
                                    'lang' => '',
                                    'field_name' => $subWidgetId,
                                    'val' => Request::input('widget_'.$widgetFieldId.'_'.$subWidgetId.'.'.$subInputId)
                                ]);
                            }
                            
                            array_push($widgetRow,$subWidget);
                        }
                        
                        continue;
                    }
                    
                    if (array_get($widgetFields,'multilang') == true) {
                        foreach(lang::codes() as $lang) {
                            array_push($widgetRow, [
                                'type' => 'value',
								'lang' => $lang,
								'field_name' => $widgetFieldId,
								'val' => Request::input('widget_'.$widgetId.'_'.$widgetFieldId.'_'.$lang.'.'.$inputId),
                            ]);
                        }
                        continue;
                    }
                    
                    array_push($widgetRow, [
                                'type' => 'value',
								'lang' => '',
								'field_name' => $widgetFieldId,
								'val' => Request::input('widget_'.$widgetId.'_'.$widgetFieldId.'.'.$inputId),
                            ]);
                }
                
                array_push($widget[$widgetId],$widgetRow);
            }
		}
        //echo '<pre>';
		//print_r($widget);
        //echo '</pre>';
        //die();
        $post = [
			'main' => $main,
			'il8n' => $il8n,
			'widget' => $widget,
			'related' => $related,
            'relation' => $relation,
            'permalink' => $permalink,
            'meta' => $meta,
			];
        //dd($post);
        return $post;
    }
    
    public function save(&$res, $callbackBefore = null, $callbackAfter = null)
    {
		$post = $this->postData();
        
        if (is_callable($callbackBefore)) {
			$post = $callbackBefore($post, $res);
		}
		
		$type = ($res->id) ? 'update' : 'create';
		$origin = $res->getOriginal();
		
		foreach ($post['main'] AS $k => $v) {
            $res->{$k} = $v;
		}
		
        return DB::transaction(function() use($type, $origin, $res, $post, $callbackAfter) {
            $res->save();

            if (method_exists($res, 'reorder'))
                $res->reorder($origin, $type);
            
            if ($type == 'update')
                $res->touch();
            
            //clear widget data
            foreach($post['widget'] as $k=>$v) {
                foreach (Widget::whereUniqid($res->id)->whereModule($this->module)->where('key',$k)->get() as $oldW) {
                    WidgetDetail::whereWidgetId($oldW->id)->delete();
                    $oldW->delete();
                }
                foreach ($v as $kk=>$vv) {
                    foreach($vv as $kkk=>$vvv) {
                        if ($vvv['type'] == 'widget') {
                            foreach(Widget::whereUniqid($res->id)->whereModule($this->module)->where('key',$kkk)->get() as $oldW) {
                                WidgetDetail::whereWidgetId($oldW->id)->delete();
                                $oldW->delete();
                            }
                        }
                    }
                }
            }

            foreach ($post['widget'] AS $k => $v) {

                foreach ($v AS $kk => $vv) {
                    $w = new Widget;
                    $w->uniqid = $res->id;
                    $w->module = $this->module;
                    $w->key = $k;
                    $w->save();

                    foreach ($vv AS $kkk => $vvv) {
                        if ($vvv['type'] == 'widget') {
                            
                            $sw = new Widget;
                            $sw->uniqid = $res->id;
                            $sw->module = $this->module;
                            $sw->key = $vvv['key'];
                            $sw->parent_id = $w->id;
                            $sw->save();
                            
                            foreach ($vvv['widgets'] as $vvvv) {
                                $swd = new WidgetDetail;
                                $swd->widget_id = $sw->id;
                                $swd->lang = $vvvv['lang'];
                                $swd->field_name = $vvvv['field_name'];
                                $swd->val = !is_null($vvvv['val']) ? $vvvv['val'] : '';
                                $swd->save();
                            }
                            
                            continue;
                        }
                        
                        $wd = new WidgetDetail;
                        $wd->widget_id = $w->id;
                        $wd->lang = array_get($vvv, 'lang');
                        $wd->field_name = array_get($vvv, 'field_name');
                        $wd->val = !is_null(array_get($vvv, 'val')) ? array_get($vvv, 'val') : '';
                        $wd->save();
                    }	
                }
            }

            Related::whereUniqid((string) $res->id)->whereModule((string) $this->module)->delete();
            foreach ($post['related'] AS $k => $v) {
                foreach ($v AS $kk => $vv) {
                    $related = new Related();
                    $related->uniqid = $res->id;
                    $related->module = $this->module;
                    $related->related = $k;
                    $related->related_uniqid = $vv;
                    $related->save();
                }
            }

            if ($post['il8n'] != []) {
                //DB::table($res->transTable)->whereBaseId($res->id)->delete();
                foreach ($post['il8n'] AS $key => $row) {
                    if (DB::table($res->transTable)->where('base_id',$res->id)->where('lang',$key)->count() >= 1) {
                        DB::table($res->transTable)->where('base_id',$res->id)->where('lang',$key)->update($row);   
                    } else {
                        $row['base_id'] = $res->id;
                        $row['lang'] = $key;
                        DB::table($res->transTable)->insert($row);
                    }
                }
            }

            if ($post['meta'] != []) {
                MetaData::whereModule($this->module)->whereUniqid($res->id)->delete();
                foreach ($post['meta'] as $lang => $meta) {
                    $m = new MetaData;
                    $m->module = $this->module;
                    $m->uniqid = $res->id;
                    $m->lang = $lang;
                    $m->meta_title = array_get($meta,'title');
                    $m->meta_keywords = array_get($meta,'keywords');
                    $m->meta_description = array_get($meta,'description');
                    $m->meta_image = array_get($meta,'image');
                    $m->save();
                }
            }

            if ($post['permalink'] != []) {
                Permalink::whereModule($this->module)->whereUniqid($res->id)->delete();
                foreach ($post['permalink'] as $lang => $permalink) {
                    $o = new Permalink;
                    $o->module = $this->module;
                    $o->uniqid = $res->id;
                    $o->lang = $lang;
                    $o->permalink = $permalink;
                    $o->compileMeta($res);
                }
            }
            
            if ($post['relation'] != []) {
                foreach ($post['relation'] as $v) {
                    DB::table($v['table'])->where($v['id_column'],'=',$res->id)->delete();
                    foreach ($v['data'] as $vv) {
                        DB::table($v['table'])->insert([
                            $v['id_column'] => $res->id,
                            $v['relation_column'] => $vv,
                        ]);
                    }
                }
            }

            if (is_callable($callbackAfter)) {
                $res = $callbackAfter($res);
                if ($res !== true) {
                    DB::rollback();
                    return $res;
                }
            }
            
            return true;
        });
    }
	
	public function delete(&$item, $callback = null)
	{
		return DB::transaction(function() use ($item, $callback) {
            
            if ($item->transTable)
                DB::table($item->transTable)->whereBaseId($item->id)->delete();
            
            $modules = [$this->module,$this->module.'-draft'];
            
            //delete widget
            foreach (Widget::whereUniqid($item->id)->whereIn('module',$modules)->get() AS $old) {
                WidgetDetail::whereWidgetId($old->id)->delete();
                $old->delete();
            }

            //delete related
            Related::whereUniqid((string) $item->id)->whereModule((string) $this->module)->delete();
            Related::whereRelatedUniqid((string) $item->id)->whereRelated((string) $this->module)->delete(); //reverse
            if (Schema::hasTable('meta_datas')) {
                MetaData::whereUniqid($item->id)->whereIn('module',$modules)->delete();   
            }
            if (Schema::hasTable('permalinks')) { //delete permalinks
                Permalink::whereUniqid($item->id)->whereIn('module',$modules)->delete();
            }
            if (Schema::hasTable('drafts')) { //delete draft
                Draft::whereUniqid($item->id)->whereIn('module',$modules)->delete();
            }
            if (Schema::hasTable('draft_logs')) {
                DraftLog::whereUniqid($item->id)->whereIn('module',$modules)->delete();
            }
            
            $origin = $item->getOriginal();
            $item->delete();

            if (method_exists($item,'reorderByDelete'))
                $item->reorderByDelete($origin);
            
            if (is_callable($callback)) {
                $res = $callback($item);
                if ($res !== true) {
                    DB::rollback();
                    return $res;
                }
            }
            
            return true;
        });
	}
	
	public function row($key, $field)
	{
        $module = $this->module;
        $res = $this->res;
        
		$html = '';
        
		if (array_get($field, 'subtitle'))
			$html .= '
				<h3 class="form-subtitle" origin="'.$key.'">'.array_get($field, 'subtitle').'</h3>';
        
        if (array_get($field, 'type') == 'widget') {
            return $html.$this->widget($key, $field, $res);
        }
		
		if (array_get($field, 'type') == 'free') {
			$html .= '
				<div class="form-group clearfix" origin="'.$key.'">'.array_get($field, 'html').'</div>';
		} else {
			$html .= '
				<div class="form-group clearfix" origin="'.$key.'" style="position:relative;">
					<label class="col-md-2 col-xs-12 control-label">'.array_get($field, 'label').'</label>
					<div class="col-md-10 col-xs-12">';
			
			if (in_array(array_get($field, 'type'), ['multicheck','multiselect'])) {
				$html .= self::input(array_get($field, 'type'), $key, Related::selected($res->id, $module, $key), $key, $field);
			} else if (in_array(array_get($field, 'type'), ['multiselect-table','multicheck-table'])) {
                $value = [];
                foreach (\DB::table($field['table'])->where($field['id_column'],$res->id)->get() as $v) {
                    $value[] = $v->{$field['relation_column']};
                }
                $html .= self::input(array_get($field, 'type'), $key, $value, $key, $field);
            } else {
				if (array_get($field, 'multilang') == true) {
					foreach (Config::get('cadmin.lang.codes') AS $lang) {
                        $field['attributes']['lang'] = $lang;
                        
                        if (array_get($field, 'type') === 'permalink') { //move to seperate function, sehingga mudah diextend
                            $v = Permalink::permalink($this->module, $res->id, $lang);    
                        } else if (array_get($field, 'meta') && in_array($field['meta'],['title','keywords','description','image'])) {
                            if (!isset($meta))
                                $meta = [];
                            if (!array_key_exists($lang,$meta))
                                $meta[$lang] = MetaData::whereModule($this->module)->whereUniqid($res->id)->whereLang($lang)->first();
                            $v = $meta[$lang] ? $meta[$lang]->{'meta_'.$field['meta']} : '';
                        } else {
                            $v = $res->translated($key, $lang);
                        }
                        
						$html .= '
							<div class="toggle-target lang-target-container" toggle-target="'.$lang.'-container" container-id="'.$key.'">'.
								self::input(array_get($field, 'type'), $key.'_'.$lang, $v, $key, $field).
							'</div>';
					}
				} else {
                    
                    if (array_get($field,'meta') == 'image') {
                        $meta = MetaData::whereModule($this->module)->whereUniqid($res->id)->orderBy('meta_image','desc')->first();
                        $html .= self::input(array_get($field, 'type'), $key, $meta ? $meta->meta_image : '', $key, $field);   
                    } else {
                       $html .= self::input(array_get($field, 'type'), $key, $res->{$key}, $key, $field);    
                    }
                    
				}
			}
			
			$html .= '
					</div>
				</div>';
		}
		
		return $html;
	}
	
	public static function input($type, $key, $value, $origin = '', $params = [])
	{
		$html = '';
		
        $attributes = array_get($params, 'attributes');
        $option = array_get($params, 'options');
        $info = array_get($params, 'info');
        $readonly = array_get($params, 'readonly');
        
        if (array_get($attributes,'cfind-thumb'))
            $info .= media::info(array_get($attributes,'cfind-thumb'));
        
		if (!is_array($attributes))
			$attributes = [];
		
		if (!array_key_exists('class', $attributes))
			$attributes['class'] = '';
		
        if (!is_array($option))
            $option = [];
        
        if (!$origin)
            $origin = $key;
        
		$attributes['class'] .= ' form-control';
		$attributes['origin'] = $origin;
        
        if (strpos(' '.$attributes['class'], 'datepicker'))
            $attributes['autocomplete'] = 'off';
		
        $readonlyValue = function($type='text') use($value,$option)
        {   
            if ($type == 'select' && !is_null($value))
                $value = array_get($option,$value);
            
            if ($type == 'multiple' && is_array($value)) {
                $r = [];
                foreach ($value as $v)
                    $r[] = array_get($option,$v);
                $value = implode(', ', $r);
            }
            
            if (is_null($value) || $value == '' || is_array($value))
                $value = ' - ';
            
            return $value;
        };
        
        if ($type == 'text' || $type == 'permalink') {
            if ($type == 'text' && $readonly == 'true' && strpos(' '.array_get($attributes,'class'),'cfind') !== false && $value) {
                $attributes['cfind-disable'] = '1';
                $html .= Form::text('',$value,$attributes);
            } else
                $html .= $readonly ? $readonlyValue() : Form::text($key, $value, $attributes);
        }
        
        if ($type == 'number')
			$html .= $readonly ? $readonlyValue() : Form::number($key, $value, $attributes);
		
        if ($type == 'password')
            $html .= $readonly ? '****' : Form::password($key, $attributes);
        
		if ($type == 'textarea')
			$html .= $readonly ? $readonlyValue() : Form::textarea($key, $value, $attributes);
        
		if ($type == 'select') 
            $html .= $readonly ? $readonlyValue('select') : Form::select($key, $option, $value, $attributes);
			
		if ($type == 'multicheck' || $type == 'multicheck-table') {
            if ($readonly) {
                $html .= $readonlyValue('multiple');
            } else {
                $html .= '<div class="related-container" origin="'.$origin.'">';
                foreach ($option AS $k => $v) {
                    $html .= '<label style="cursor:pointer;font-weight:normal">'.Form::checkbox($key.'[]', $k, (in_array($k, $value) ? true : false), $attributes).' '.$v.'</label><br>';
                }
                $html .= '</div>';
            }
		}
        
        if ($type == 'multiselect-table' || $type == 'multiselect') {
            if ($readonly) {
                $html .= $readonlyValue('multiple');
            } else {
                $attributes['multiple'] = true;

                $html .= '<select name="'.$key.'[]"';
                foreach ($attributes as $k=>$v)
                    $html .= $k.'="'.$v.'"';
                $html .= '>';
                foreach ($option as $k=>$v) {
                    $html .= '<option value="'.e($k).'" '.((in_array($k,$value)) ? 'selected' : '').'>'.e($v).'</option>';   
                }
                $html .= '</select>';
            }
        }
		
		if ($info)
			$html .= '<em class="form-info">'.$info.'</em>';
		
		return $html;
	}
	
	protected function widget($key, $widget, $res, $parentId = null)
	{
		//send to widgetSource
        $this->widgets[$key] = $widget;
        foreach(array_get($widget,'widgets') as $k=>$v) { //including sub-widget
            if (array_get($v, 'type') == 'widget')
                $this->widgets[$k] = $v;
        }
        
		//open widget
		$html = '
			<div class="widgets" widget-source="'.$key.'" widget-min="'.array_get($widget, 'min').'" widget-max="'.array_get($widget, 'max').'">
				<div class="widget-container" widget-source="'.$key.'">';
		
		//define count, min & max
        $min = array_get($widget,'min');
        if (!$min)
            $min = 0;
        $max = array_get($widget,'max');
        $count = 0;
		
		$draw = function($obj,$name,&$count,$parentId=null) use($key,$widget,$res)
		{
			$html = '
				<div class="widget-row">';
			if (array_get($widget,'readonly') != true) {
				$html .= self::widgetTool();
			}
			$html .= Form::hidden('widget_'.$key.'['.$name.']','',['class'=>'widget-id']);
			foreach (array_get($widget, 'widgets') AS $kk => $field) {
				$field['readonly'] = array_get($widget,'readonly');
				if (array_get($field,'type') == 'widget') {
					$html .= $this->widget($kk, $field, $res,$parentId);
					continue;
				}
				$html .= self::widgetRow($key,$kk,$field,$obj,$name);
			}
			$html .= '
				</div>';
			$count++; //keep counting
			return $html;
		};
		
		if ($res->id) { //load saved widget
			foreach (Widget::whereUniqid($res->id)->whereModule($this->module)->where('key', $key)->whereParentId($parentId)->get() AS $row) {
				$html .= $draw($row,'old_'.$row->id,$count,$row->id);
			}
		}
        
        while($count < $min) { //auto-add item if count less than min
			$html .= $draw(new Widget, 'auto_'.uniqid(),$count);
        }
		
		//close widtet-container
        $html .= '</div>';
        
		//more button
        $more = 'more';
        if (array_get($widget,'more-label'))
            $more = $widget['more-label'];
		$moreStyle = ($max && $count >= $max) ? 'style="display:none;"' : '';
        if (array_get($widget,'readonly') != true)
            $html .= '<a href=# class="widget-add btn bg-purple btn-flat" '.$moreStyle.'><i class="fa fa-plus"></i> '.$more.'</a>';
        
		//close widget
		$html .= '</div>';
				
        return $html;
	}
    
    public function widgetSource()
	{
		$widgets = $this->widgets;
		
		$html = '
			<div style="display:none;" class="col-md-12 col-xs-12">';
		
		$res = new Widget;
		
		foreach ($widgets AS $key => $widget) {
			$html .= '
				<div class="widget-source" widget-source="'.$key.'">
				'.self::widgetTool().Form::hidden('widget_'.$key.'[]','',['class'=>'widget-id']);
            
			foreach (array_get($widget, 'widgets') AS $kk => $field) {
                
                if (array_get($field,'type') == 'widget') { //auto draw sub-widget
                    
                    $html .= $this->widget($kk, $field, $res);
                    
                    continue;
                }
                
                //draw default
				$html .= self::widgetRow($key, $kk, $field, $res);
			}
			$html .= '
				</div>';
		}
		
		$html .= '
			</div>';
        
        //append css for auto hide remove btn
        $html .= '<style>';
        foreach($widgets as $key=>$widget) {
            for($i=1;$i<=(int) array_get($widget,'min');$i++) {
                $html .= '.widgets[widget-source="'.$key.'"] > .widget-container > .widget-row:nth-child('.$i.') > .widget-tools .widget-remove { display:none; }'.PHP_EOL;
            }
        }
        $html .= '</style>';
        
		return $html;
	}
	
	public static function widgetRow($key, $fieldKey, $field, $res,$idx='')
	{
		$html = '
			<div class="form-group clearfix" origin="widget_'.$key.'_'.$fieldKey.'">
				<label class="col-md-2 col-xs-12 control-label">'.array_get($field, 'label').'</label>
				<div class="col-md-10 col-xs-12">';
		
		if (array_get($field, 'multilang') == true) {
			foreach (Config::get('cadmin.lang.codes') AS $lang) {
                $field['attributes']['lang'] = $lang;
				$html .= '
					<div class="toggle-target lang-target-container" toggle-target="'.$lang.'-container" container-id="'.$key.'">'.
						self::input(array_get($field, 'type'), 'widget_'.$key.'_'.$fieldKey.'_'.$lang.'['.$idx.']', $res->value($fieldKey, $lang), 'widget_'.$key.'_'.$fieldKey, $field).
					'</div>';
			}
		} else {
			$html .= self::input(array_get($field, 'type'), 'widget_'.$key.'_'.$fieldKey.'['.$idx.']', $res->value($fieldKey), 'widget_'.$key.'_'.$fieldKey, $field);
		}
				
		$html .= '
				</div>
			</div>';
			
		return $html;
	}
	
	public static function widgetTool()
	{
		return '<div class="widget-tools">
				<a href=# class="widget-sort-down" ><i class="fa fa-chevron-down"></i></a>
				<a href=# class="widget-sort-up" ><i class="fa fa-chevron-up"></i></a>
				<a href=# class="widget-remove" ><i class="fa fa-trash-o"></i></a>
			</div>';
	}
}