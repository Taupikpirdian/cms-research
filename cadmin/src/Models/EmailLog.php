<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Models\EmailLogAttachment;

class EmailLog extends Model
{
    protected $table = 'email_logs';
    
    public function attachments()
    {
        return $this->hasMany(EmailLogAttachment::class);
    }
}