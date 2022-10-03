<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Models\Privilege;

class ModulePrivilege extends Model
{
    protected $table = 'module_privileges';
    
    public function privilege()
	{
		return $this->belongsTo(Privilege::class);
	}
}
