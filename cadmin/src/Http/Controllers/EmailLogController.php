<?php namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

use Cactuar\Admin\Models\EmailLog;
use Cactuar\Admin\Models\EmailLogAttachment;
use Cactuar\Admin\Traits\Models\BaseTrait;
use Cactuar\Admin\Traits\Models\ListingTrait;
use Cactuar\Admin\Traits\Models\DownloadTrait;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Models\Conf;
use Cactuar\Admin\Helpers\media;
use Request;

class EmailLogController extends Controller
{
    use BaseTrait, ListingTrait, DownloadTrait {
        getIndex as listingIndex;   
    }
    
    public function listingRes()
    {
        return EmailLog::select();
    }
    
    public function listingFields()
    {
        return ['email' => 'To', 'subject' => 'Subject'];
    }
    
    public function listingOrders()
    {
        return ['email' => 'To', 'subject' => 'Subject', 'created_at' => 'Created'];
    }
	
	public function listingOrderDefault($q)
	{
		return $q->orderBy('id', 'desc');
	}
    
    public function listingSearchs()
    {
        return ['email', 'subject'];
    }
    
    public function listingTimes()
    {
        return ['created_at' => 'Created'];
    }
    
    public function listingRanges()
    {
        return ['created_at'];
    }
}