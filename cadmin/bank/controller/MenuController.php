<?php  namespace App\Http\Controllers\Admin;

use	Cactuar\Admin\Models\Menu;
use Cactuar\Admin\Http\Controllers\MenuController as BaseController;

class MenuController extends BaseController
{
	protected $templates = [
        'template-1' => [
            'label' => 'Template #1',
            'preview' => 'https://www.google.co.id/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
            'widgets' => [
                'banner' => [
                    'subtitle' => 'Banner',
                    'widgets' => [
                        'image' => [
                            'type' => 'text',
                        ]
                    ],
                    'max' => 1
                ]
            ]
        ],
        'template-2' => [
            'label' => 'Template #2',
            'preview' => 'https://www.google.co.id/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
        ]
    ];
    
    protected $deep = 3;
    
    public function draftAble($item)
    {
        return !in_array($item->type, ['blank','url']);
    }
}