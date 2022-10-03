<?php  namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Cactuar\Admin\Helpers\cfind;
use Config;

class MediaFileController extends Controller
{
    public function getIndex()
    {
        return $this->anyFile();
    }
    
    public function postIndex()
    {
        return $this->anyFile();
    }
    
    public function anyFile()
    {   
        echo cfind::install([
            'types' => [
                'files' => [
                    'ext' => [
                            'doc',
                            'docx',
                            'pdf',
                        ]
                    ],
                ],

            //'rootPath'	=> Config::get('cadmin.media.sourcepath'), 
            //'rootURL'	=> preg_replace('#/+#','/',dirname(array_get($_SERVER,'PHP_SELF')).'/'.Config::get('cadmin.media.sourcepath')),
            'view'	=> ((Request::query('opener')) ? 'cactuar::admin.cfind-opener' : 'cactuar::admin.cfind' ),
            'disable' => ['add' => !Auth::user()->allow('file', 'create'), 'delete' => !Auth::user()->allow('file', 'delete')]
            ])->draw();
    }
}