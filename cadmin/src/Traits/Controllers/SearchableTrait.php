<?php namespace Cactuar\Admin\Traits\Controllers;

use Cactuar\Admin\Helpers\lang;

trait SearchableTrait
{
	public function searchable($post,$obj,$type)
	{
		$searchs = [];
		$langs = lang::codes();
		
		foreach ($langs as $v)
			$searchs[$v] = '';
		$searchs['plains'] = '';
		
		$clean = function($str)
		{
			if (!$str)
				return $str;
			return strip_tags(str_replace(PHP_EOL, ' ', $str)).' ';
		};
		
		$fields = $this->fields($type);
		
		if (is_array(array_get($post, 'main'))) {
			foreach ($post['main'] as $k=>$v) {
				if (!in_array(array_get($fields, $k.'.type'), ['text','textarea']))
					continue;
				$searchs['plains'] .= $clean($v);
			}
		}
		
		if (is_array(array_get($post, 'il8n'))) {
			foreach ($post['il8n'] as $lang=>$rows) {
				foreach ($rows as $k=>$v) {
					if (!in_array(array_get($fields, $k.'.type'), ['text','textarea']))
						continue;
					$searchs[$lang] .= $clean($v);
				}
			}
		}
		
		if (is_array(array_get($post, 'widget'))) {
			foreach ($post['widget'] as $k=>$widgets) {
				foreach ($widgets as $items) {
					foreach ($items as $item) {
						if (!in_array(array_get($fields, $k.'.widgets.'.$item['field_name'].'.type'), ['text','textarea']))
							continue;
						if ($item['lang'])
							$searchs[$item['lang']] .= $clean($item['val']);
						else
							$searchs['plains'] .= $clean($item['val']);
					}
					
				}
			}
		}
        
        if (is_array(array_get($post,'meta'))) {
            foreach ($post['meta'] as $k=>$v) {
                foreach ($v as $vv)
                    $searchs[$k] .= $clean($vv);
            }
        }
		
		foreach (lang::codes() as $v) {
			$searchs[$v] .= $searchs['plains'];
			if (method_exists($obj,'translated'))
				$post['il8n'][$v]['searchable'] = $searchs[$v];
			else
				$post['main']['searchable'] = $searchs[$v];
		}
		
		return $post;
	}
}