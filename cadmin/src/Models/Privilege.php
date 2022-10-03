<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Models\User;
use Cactuar\Admin\Models\ModulePrivilege;

class Privilege extends Model
{
    protected $table = 'privileges';
    
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function modules()
    {
        return $this->hasMany(ModulePrivilege::class);
    }
    
    public function getDelAbleAttribute()
    {
        return $this->users->count() < 1;
    }
}
