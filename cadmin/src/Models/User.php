<?php namespace Cactuar\Admin\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Cactuar\Admin\Models\Privilege;
use Config;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];
    
    public function privilege()
    {
        return $this->belongsTo(Privilege::class);
    }
    
    public function getIsRootAttribute()
    {
        return $this->privilege_id === null;
    }
    
    public function allow($module, $action)
    {
        if ($this->isRoot) return true;
        
        if (in_array($action,['order-up', 'order-down']))
            $action = 'edit';
        
        foreach ($this->privilege->modules AS $mod) {
            if ($mod->module == $module && $mod->act == $action)
                return true;
        }
        
        $public = config('cadmin.menu.'.$module.'.public-permission');
        if (is_array($public)
           && in_array($action,$public)
           && $this->privilege->modules()->whereModule($module)->count() >= 1
           ) {
            return true;
        }
        
        return false;
    }
    
    public function allowOrDie($module, $action)
    {
        if (!$this->allow($module, $action))
            abort(403);
    }
    
    public function passwordHistory($action = 'edit')
    {
        $res = new UserPasswordHistory;
        $res->user_id = $this->id;
        $res->password = $this->password;
        $res->action = $action;
        $res->save();
    }
    
    public function passwordHistories()
    {
        return $this->hasMany(UserPasswordHistory::class);
    }
    
    public function passwordLastTime()
    {
        $history = $this->passwordHistories()->orderBy('id','desc')->first();
        if (!$history)
            return $this->created_at;
        return $history->created_at;
    }
    
    public function passwordDay()
    {
        return round((time() - strtotime($this->passwordLastTime())) / 86400);
    }
}
