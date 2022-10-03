<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Traits\Models\TranslateTrait;

class EmailTemplate extends Model
{
    use TranslateTrait;
    
    protected $table = 'email_templates';
    protected $fillable = ['purpose', 'type'];
}