<?php namespace Cactuar\Admin\Traits\Models;

use Cactuar\Admin\Models\Draft;

trait ModelHelperTrait
{
    protected $tempDraft = null;

    public function widget($key,$multiple = true)
    {
        return widget($this->id,$this->module,$key,$multiple);
    }

    public function conf($key)
    {
        return conf($key);
    }

    public function widget_conf($key,$multiple = true)
    {
        return widget_conf($this->module,$key,$multiple);
    }
    
    public function draft()
    {
        if (!is_null($this->tempDraft))
            return $this->tempDraft;
        return $this->tempDraft = Draft::initial($this->id,$this->module,$this);
    }
    
    public function draftWidget($key,$multiple = true)
    {
        return Draft::widget($this->module,$this->id,$key,$multiple);
    }
}