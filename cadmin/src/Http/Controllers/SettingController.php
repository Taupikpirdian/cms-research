<?php namespace Cactuar\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

use Cactuar\Admin\Traits\Controllers\BaseTrait;
use Cactuar\Admin\Traits\Controllers\ConfTrait;

class SettingController extends Controller
{
    use BaseTrait, ConfTrait;
    
    public function configFields()
    {
		return [
            'status' => [
                'label' => 'Status',
                'type' => 'select',
                'options' => ['online' => 'online', 'offline' => 'offline'],
            ],
            'logo' => [
                'label' => 'Logo',
                'type' => 'text',
                'attributes' => ['class' => 'required cfind', 'cfind-type' => 'image'],
            ],
			'favicon' => [
				'label' => 'Fav Icon',
				'type' => 'text',
                'info' => 'PNG or ICO file only',
				'attributes' => ['class' => 'required cfind', 'cfind-type' => 'image', 'cfind-ext' => 'png,ico'],
			],
            'copyright' => [
                'label' => 'Copyright',
                'type' => 'text',
                'attributes' => ['class' => 'required'],
                'info' => 'available keywords:<br>[year] : current year<br>[webarq] : webarq copyright',
            ],
            /*'use-www' => [ //disabled as request, recommend redirect by htaccess / cpanel
                'label' => 'Domain',
                'type' => 'select',
                'options' => [0 => 'without www.*', 1 => 'with www.*'],
                'attributes' => [
                    'style' => 'max-width:200px',
                ]
            ],*/
            'meta-title' => [
                'label' => 'Meta Title',
                'subtitle' => 'Default Meta Data',
                'type' => 'text',
                'multilang' => true,
                'attributes' => ['class' => 'required', 'maxlength'=>60],
                'info' => 'maximum 60 character',
            ],
            'meta-keywords' => [
                'label' => 'Meta Keywords',
                'type' => 'textarea',
                'multilang' => true,
                'attributes' => ['rows' => 3],
            ],
            'meta-description' => [
                'label' => 'Meta Description',
                'type' => 'textarea',
                'multilang' => true,
                'attributes' => ['class' => 'required', 'rows' => 3, 'maxlength'=>160],
                'info' => 'maximum 160 character',
            ],
            'meta-image' => [
                'label' => 'Meta Image',
                'type' => 'text',
                'attributes' => ['class' => 'cfind', 'cfind-type' => 'image'],
            ],
            'analytic-scripts' => [
                'label' => 'Analytic Script',
                'type' => 'textarea',
            ],
            'schema' => [
                'label' => 'Schema.org Script',
                'type' => 'textarea',
            ],
            'head-scripts' => [
                'label' => 'Embed Script (Head)',
                'type' => 'textarea',
            ],
            'body-scripts' => [
                'label' => 'Embed Script (Body)',
                'type' => 'textarea',
            ]
        ];
    }
}