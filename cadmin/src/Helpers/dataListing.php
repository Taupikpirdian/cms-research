<?php namespace Cactuar\Admin\Helpers;

class dataListing
{
    private $model;
    private $params = [];
    
    public static function initial($model,$fields)
    {
        return new dataListing($model,$fields);
    }
    
    public function __construct($model,$fields)
    {
        $this->model = $model;    
        $this->params['fields'] = $fields;
        $this->params['filters'] = [];
        $this->params['searchs'] = [];
        $this->params['orders'] = [];
        $this->params['ranges'] = [];
        $this->params['title'] = '';
        $this->params['download'] = false;
        
        $this->params['actions'] = function($item)
        {
            return [];
        };
        
        $this->params['callback'] = function($key,$value,$item,$type)
        {
            return e($value);  
        };
        
        $this->params['order-default'] = function($q)
        {
            return $q->orderBy('id','desc');
        };
        
        return $this;
    }
    
    public function filters($filters)
    {
        $this->params['filters'] = $filters;
        return $this;
    }
    
    public function searchs($searchs)
    {
        $this->params['searchs'] = $searchs;
        return $this;
    }
    
    public function orders($orders)
    {
        $this->params['orders'] = $orders;
        return $this;
    }
    
    public function ranges($ranges)
    {
        $this->params['ranges'] = $ranges;
        return $this;
    }
    
    public function callback($callback)
    {
        if (!is_callable($callback))
            return $this;
        $this->params['callback'] = $callback;
        return $this;
    }
    
    public function actions($actions)
    {
        if (!is_callable($actions))
            return $this;
        $this->params['actions'] = $actions;
        return $this;
    }
    
    public function orderDefault($callback)
    {
        if (!is_callable($callback))
            return $this;
        $this->params['order-default'] = $callback;
        return $this;
    }
    
    public function title($title)
    {
        $this->params['title'] = $title;
        return $this;
    }
    
    public function allowDownload($download = true)
    {
        $this->params['download'] = $download;
        return $this;
    }
    
    public function render($full = true)
    {
        $allowDownload = $this->params['download'];
        $download = $allowDownload;
        
        $type = 'html';
        $res = $this->model;
        
        if (request()->get('sort')) {
            $ex = explode('-',request()->get('sort'));
            $sortType = array_pop($ex);
            $sort = implode('-',$ex);
            
            if ($sort && array_key_exists($sort, $this->params['orders']) && in_array($sortType, ['asc', 'desc']))
                $res->orderBy($sort, $sortType);
        }
        
        if (request()->get('range') && !empty($this->params['ranges'])) {
            $range = explode(' - ', request()->get('range'));
            if (count($range) == 2) {
                $range[0] = date('Y-m-d', strtotime($range[0]));
                $range[1] = date('Y-m-d', strtotime($range[1]));
                
                $keys = $this->params['ranges'];
                $res->where(function($q) use($keys,$range) {
                    foreach ($keys as $f) {
                        $q->orWhereBetween($f,[$range[0].' 00:00:00', $range[1].' 23:59:59']);
                    }
                    return $q;
                });
            }
        }
        
       if (request()->get('search') && !empty($this->params['searchs'])) {
            $searchs = $this->params['searchs'];
            $res->where(function($q) use($searchs) {
                foreach ($searchs AS $f) {
                    $q->orWhere($f, 'like', '%'.request()->get('search').'%');
                }
                return $q;
            });
            
        }
        
        if (is_array(request()->get('filter')) && !empty($this->params['filters'])) {
            $filter = [];
            foreach (request()->get('filter') as $item) {
                $ex = explode('-', $item);
                
                if (count($ex) < 2)
                    continue;
                
                $k = array_shift($ex);
                $v = implode('-',$ex);
                if (!array_key_exists($k, $filter))
                    $filter[$k] = [];
                
                $filter[$k][] = $v;
            }
            
            foreach ($this->params['filters'] as $k => $v) {
                if (array_key_exists($k, $filter))
                    $res->whereIn($k, $filter[$k]);
            }
        }
        
        $res = $this->params['order-default']($res);
        
        if (request()->queryValidated('download','string') != 'csv')
            $download = false;
        
        if ($download) {
            $res = $res->get();
            $type = 'download';
        } else
            $res = $res->paginate(30);
        
        $heads = [];
        foreach ($this->params['fields'] as $k=>$v)
            $heads[$k] = $v;
        
        $data = [];
        
        foreach($res as $item) {
            $row = [];
            
            foreach ($this->params['fields'] as $k=>$v) {
                $value = $this->params['callback']($k,$item->{$k},$item,$type);
                if (is_null($value))
                    $value = e($item->{$k});
                $row[$k] = $value;
            }
            
            $actions = '';
            foreach ($this->params['actions']($item) as $v)
                $actions .= $v;
            if ($actions) {
                $row['action'] = $actions;
                if (!array_key_exists('action',$heads))
                    $head['actions'] = '';
            }
            
            $data[] = $row;
        }
        
        if ($download) 
            return $this->download($heads,$data);
        
        return view($full == true ? 'cactuar::admin.data-listing-container' : 'cactuar::admin.data-listing',[
            'tableTitle' => $this->params['title'],
            'download' => $allowDownload,
            'searchs' => $this->params['searchs'],
            'filters' => $this->params['filters'],
            'orders' => $this->params['orders'],
            'ranges' => $this->params['ranges'],
            'listings' => $res,
            'heads' => $heads,
            'data' => $data,
        ]);
    }
    
    private function download($heads,$data)
    {
        $csv = [$heads];
        foreach($data as $v)
            $csv[] = $v;
        
        return \Cactuar\Admin\Helpers\helper::csv($csv,uniqid());
    }
}