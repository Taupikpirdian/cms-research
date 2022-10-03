<?php namespace Cactuar\Admin\Traits\Controllers;

use Request;
use Auth;
use Config;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\Draft;

trait ListingTrait
{
        
    public function getIndex()
    {
        $data = $this->_listingCompile('html');
        
        return view('cactuar::admin.listing', compact('data'));
    }
    
    protected function _listingCompile($type = 'html')
    {
        $module = $this->module();
        $label = $this->label('listing');
        $res = $this->listingRes($type);
		$order = $this->listingOrders();
		
        //do sort
        if (Request::query('sort') && array_key_exists(Request::query('sort'), $order) && in_array(Request::query('sortType'), ['asc', 'desc']))
            $res->orderBy(Request::query('sort'), Request::query('sortType'));
        
        $res = $this->listingOrderDefault($res);
        
        //do search
        if (Request::query('search')) {
            if (method_exists($this, 'listingCallbackSearch')) //by callback
                $res = $this->listingCallbackSearch($res, Request::query('search'));
            else { //by array
                $searchs = $this->listingSearchs();
                $res->where(function($q) use($searchs) {
                    foreach ($searchs AS $f) {
                        // $q->orWhere($f, 'like', '%'.Request::query('search').'%');
                        if(env('DB_CONNECTION') !== 'pgsql'){
                            //this is condition if you want to make search keywords that contains case insensitive with using DB CONNECTION except postgreSQL (pgsql) 
                            //there is no issue if we want to make search keywords that contains case insensitive using this where statement (below)
							$q->orWhere($f, 'like', '%'.Request::query('search').'%');
						}

						if(env('DB_CONNECTION') == 'pgsql'){
                            //this is condition if you want to make search keywords that contains case insensitive with using DB CONNECTION with postgreSQL (pgsql)
                            //there is some issue that we can not make search keywords that contains case insensitive using 'like' in postgreSQL (pgsql)
                            //so, the solution is we should using 'ilike' to make search keywords that contains case insensitive
							$q->orWhere($f, 'ilike', '%'.Request::query('search').'%');
						}
                    }
                    return $q;
                });
            }
        }
        
        //do range
        if (Request::input('range')) {
            $range = explode(' - ', Request::input('range'));
            if (count($range) == 2) {
                $range[0] = date('Y-m-d', strtotime($range[0]));
                $range[1] = date('Y-m-d', strtotime($range[1]));
                
                if (method_exists($this, 'listingCallbackRange')) //by callback
                    $res = $this->listingCallbackRange($res, $range[0], $range[1]);
                else {
                    $keys = $this->listingRanges();
                    $res->where(function($q) use($keys,$range) {
                        foreach ($keys as $f) {
                            $q->orWhereBetween($f,[$range[0].' 00:00:00', $range[1].' 23:59:59']);
                        }
                        return $q;
                    });
                }
            }
        }
        
        //do filters
        if (is_array(Request::input('filter'))) {
            $filter = [];
            foreach (Request::input('filter') as $item) {
                $ex = explode('-', $item);
                
                if (count($ex) < 2)
                    continue;
                
                $k = array_shift($ex);
                $v = implode('-',$ex);
                if (!array_key_exists($k, $filter))
                    $filter[$k] = [];
                
                $filter[$k][] = $v;
            }
            
            foreach ($this->listingFilters() as $k => $v) {
                if (array_key_exists($k, $filter))
                    $res->whereIn($k, $filter[$k]);
            }
        }
        
        //\DB::enableQueryLog();
        if ($type == 'html')
            $listing = $res->paginate(30);
		else
            $listing = $res->get();
        //dd(\DB::getQueryLog());
        
        //construct head
        $head = [];
		
		$actSort = false; 
		
		foreach ($this->listingFields($type) AS $k => $v) {
            $head[$k] = $v;
        }
        
		foreach ($listing AS $v) {
			if (method_exists($v, 'orderUp')) {
				$actSort = true;
				break;
			}
		}
		
		if ($actSort == true) {
			$head['action-sort'] = 'Sort ID';
        }
		
		foreach ($this->listingTimes() AS $k => $v) {
			$head['time-'.$k] = $v;
		}
		
		$actionExists = false;
		
		$data = [];
        
        //construct data
        foreach ($listing AS $item) {
            $row = ['uniqid' => $item->id];
			$acts = [];
            foreach ($this->listingFields($type) AS $k => $v) {
                $row[$k] = $this->listingCallback($k, $item->{$k}, $item, $type);
                if (is_null($row[$k])) {
                    if ($type == 'html')
                        $row[$k] = e($item->{$k});
                    else
                        $row[$k] = $item->{$k};
                }
            }
            
            if ($actSort) {
				$row['action-sort'] = $item->sort_id;
				
				if ($type == 'html' && Auth::user()->allow($module, 'edit')) {
                    $row['action-sort'] .= ''.($item->sort_id < $item->maxSort ? '&nbsp;&nbsp;<a 
															href='.\Cactuar\Admin\Helpers\admin::url($module.'/order-up?unique='.$item->id).' 
															class="fa fa-chevron-down need-confirm"
															data-confirm="Are you sure to reorder this item?"
															></a>' : '').
											($item->sort_id > 1 ? '&nbsp;&nbsp;<a 
															href="'.\Cactuar\Admin\Helpers\admin::url($module.'/order-down?unique='.$item->id).'" 
															class="fa fa-chevron-up need-confirm"
															data-confirm="Are you sure to reorder this item?"
															></a>' : '');
				}
			}
			
			foreach ($this->listingTimes() AS $k => $v) {
                $row['time-'.$k] = date('d M Y H:i', strtotime($item->{$k}));
                if (strtotime($item->{$k}) <= 0)
				    $row['time-'.$k] = '-';
            }
			
			if ($type == 'html') {
				foreach ($this->listingCustomActs($item) AS $k=>$act) {
					$acts[$k] = $act;
				}
                
				if (
					method_exists($this, 'getPublish')
					&& Auth::user()->allow($module, 'publish') 
					&& (
						!method_exists($this, 'publishAble') 
						|| $this->publishAble($item)
					)
				) {
					if ($item->is_active) {
						$acts['unpublish'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=0&unique='.$item->id).'" 
											   class="btn bg-yellow btn-flat need-confirm" 
											   data-confirm="Are you sure to unpublish selected item?">
											<i class="fa fa-toggle-on"></i> Published
										</a>';
					} else {
						$acts['publish'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/publish?publish=1&unique='.$item->id).'" 
											   class="btn bg-gray btn-flat need-confirm" 
											   data-confirm="Are you sure to publish selected item?">
											<i class="fa fa-toggle-off"></i> Unpublished
										</a>';
					}
				}
                
                if (
					method_exists($this, 'getRead')
					&& Auth::user()->allow($module, 'read')
					&& (
						!method_exists($this, 'readAble')
						|| $this->readAble($item)
					)
				) {
					if ($item->is_read) {
						$acts['unread'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/read?read=0&unique='.$item->id).'" 
											   class="btn bg-yellow btn-flat need-confirm" 
											   data-confirm="Are you sure to mark selected item as unread?">
											<i class="fa fa-toggle-on"></i> Read
										</a>';
					} else {
						$acts['read'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/read?read=1&unique='.$item->id).'" 
											   class="btn bg-gray btn-flat need-confirm" 
											   data-confirm="Are you sure to mark selected item as read?">
											<i class="fa fa-toggle-off"></i> Unread
										</a>';
					}
				}
				
				if (
					method_exists($this, 'getEdit')
					&& Auth::user()->allow($module, 'edit') 
					&& (
						!method_exists($this, 'editAble') 
						|| $this->editAble($item)
					)
				) {
					$acts['edit'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/edit?unique='.$item->id).'" class="btn bg-purple btn-flat">
											<i class="fa fa-edit"></i> Edit
										</a>';
				}
                
                if (
                    method_exists($this, 'getDraft')
                    && Auth::user()->allow($module, 'draft')
                    && (
                        !method_exists($this, 'draftAble') 
						|| $this->draftAble($item)
                    )
                ) {
                    $acts['draft'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/draft?unique='.$item->id).'" class="btn bg-aqua btn-flat">
											<i class="fa fa-edit"></i> Draft
										</a>';
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
				
				if (
					method_exists($this, 'getDelete')
					&& Auth::user()->allow($module, 'delete')
					&& (
						!method_exists($this, 'deleteAble') 
						|| $this->deleteAble($item)
					)
				) {
					$acts['delete'] = '<a href="'.\Cactuar\Admin\Helpers\admin::url($module.'/delete?unique='.$item->id).'" 
											   class="btn bg-red btn-flat need-confirm" 
											   data-confirm="Are you sure to delete this item?">
											<i class="fa fa-trash-o"></i> Delete
										</a>';
				}
                
                $this->listingOverwriteActs($acts,$item);
				
				if ($acts != []) {
					$actionExists = true;
					$row['action'] = implode('&nbsp;', $acts);
				}
			}
			
            array_push($data, $row);
        }
		
		if ($actionExists && $type == 'html') 
			$head['action'] = '&nbsp;';
        
		$bulkAction = [];
        foreach ($this->listingBulkAction() as $k => $v) {
            if (is_array($v) && array_get($v, 'target') && array_get($v, 'label'))
                $bulkAction[$k] = $v;
        }
        
        return [
				'label' => $label,
				'head' => $head,
				'data'  => $data,
				'listing' => $listing,
				'search' => ($this->listingSearchs() != [] || method_exists($this, 'listingCallbackSearch')) ? true : false,
                'range' => ($this->listingRanges() != [] || method_exists($this, 'listingCallbackRange')) ? true : false,
				'order' => $order,
				'menu' => $this->baseMenu($module, 'listing'),
                'subMenu' => $this->subMenu($module, 'listing'), 
			    'filter' => method_exists($this, 'listingFilters') ? $this->listingFilters() : [],
                'append' => $this->listingAppend(),
                'bulkAction' => $bulkAction,
            ];
			
        return $data;
    }
    
    public function listingMainMenu($menu)
    {
        return $menu;
    }
	
	public function getOrderUp()
	{
		$res = $this->listingRes('order');
		$item = $res->findOrFail(Request::query('unique'));
		
		$item->orderUp();
        
        event(new \Cactuar\Admin\Events\CudAfter($this->module(),$item,'order-up'));

		$module = $this->module();
		
		$log = $this->log($item, 'edit');
		Log::write($module, 'reorder', $item->id, $log);
		
		return back()->with('success', 'successfull reorder item');
	}
	
	public function getOrderDown()
	{
		$res = $this->listingRes('order');
		$item = $res->findOrFail(Request::query('unique'));
		
        $item->orderDown();
        
        event(new \Cactuar\Admin\Events\CudAfter($this->module(),$item,'order-down'));
		
		$module = $this->module();
		
		$log = $this->log($item, 'edit');
		Log::write($module, 'reorder', $item->id, $log);
		
		return back()->with('success', 'successfull reorder item');
	}
    
	public function listingCustomActs($row)
	{
		return [];
	}
    
    public function listingOverwriteActs(&$acts,$item)
    {
        
    }
	
    public function listingRes($type = 'html')
    {
        return null;
    }
    
    public function listingFields($type = 'html')
    {
        return [];
    }
    
    public function listingTimes()
    {
        return ['created_at' => 'Created', 'updated_at' => 'Updated'];
    }
    
    public function listingSearchs()
    {
        return [];
    }
    
    public function listingRanges()
    {
        return [];
    }
    
    public function listingOrders()
    {
        return [];
    }
    
    public function listingFilters()
    {
        return [];
    }
    
    public function listingOrderDefault($res)
    {
        return $res->orderBy('id', 'asc');
    }
    
    public function listingCallback($key, $value, $row, $type = 'html')
    {
		return null; //default null, auto sanitize on '_compile'
    }
	
	public function listingAppend()
	{
		return '';
	}
    
    public function listingBulkAction()
    {
        return [];
    }
}