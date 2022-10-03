<?php  namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Helpers\cfind;
use Config;

class MediaImageController extends Controller
{
    public function getIndex()
    {
        return $this->anyImage();
    }
    
    public function postIndex()
    {
        return $this->anyImage();
    }
    
    public function anyImage()
    {   
        echo cfind::install([
            'types' => [
                'images' => [
                    'ext' => [
                            'jpg',
                            'jpeg',
                            'bmp',
                            'gif',
                            'png',
							'ico',
                        ]
                    ],
                ],

            //'rootPath'	=> Config::get('cadmin.media.sourcepath'), 
            //'rootURL'	=> preg_replace('#/+#','/',dirname(array_get($_SERVER,'PHP_SELF')).'/'.Config::get('cadmin.media.sourcepath')),
            'view'	=> ((Request::query('opener')) ? 'cactuar::admin.cfind-opener' : 'cactuar::admin.cfind' ),
            'disable' => ['add' => !Auth::user()->allow('image', 'create'), 'delete' => !Auth::user()->allow('image', 'delete')]
            ])->draw();
    }
}