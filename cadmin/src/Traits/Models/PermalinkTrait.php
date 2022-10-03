<?php namespace Cactuar\Admin\Traits\Models;

use Cactuar\Admin\Models\Permalink as Model;
use Cactuar\Admin\Helpers\lang;

trait PermalinkTrait
{
    //protected $permalinkModule = '';
    
    public function getPermalinkAttribute()
    {
        if (!$this->permalinkModule)
            $this->permalinkModule = $this->module;
        return Model::permalink($this->permalinkModule,$this->id,lang::active()); 
    }
}