<?php namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Cactuar\Admin\Helpers\media;

class ThumbController extends Controller 
{
    public function getIndex()
    {
        return response()->file(media::thumbMake());
    }
}