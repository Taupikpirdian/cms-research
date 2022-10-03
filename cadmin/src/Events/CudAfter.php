<?php namespace Cactuar\Admin\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CudAfter
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $module;
    public $item;
    public $type;

    public function __construct($module,$item,$type)
    {
        $this->module = $module;
        $this->item = $item;
        $this->type = $type;
    }
}