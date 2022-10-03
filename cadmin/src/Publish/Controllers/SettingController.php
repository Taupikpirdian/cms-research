<?php namespace App\Http\Controllers\Admin;

use Cactuar\Admin\Http\Controllers\SettingController as BaseController;

class SettingController extends BaseController
{
    public function configFields()
    {
        $fields = parent::configFields();
        
        $fields['email-from'] = [
            'type' => 'text',
            'label' => 'From Email',
            'subtitle' => 'Sending Email',
            'attributes' => [
                'class' => 'required email'
            ]
        ];
        $fields['email-from-name'] = [
            'type' => 'text',
            'label' => 'From Name',
            'attributes' => [
                'class' => 'required',
            ]
        ];
        
        return $fields;
    }
}