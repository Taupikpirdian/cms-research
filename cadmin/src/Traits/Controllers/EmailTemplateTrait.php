<?php namespace Cactuar\Admin\Traits\Controllers;

use Cactuar\Admin\Helpers\adminForm;
use Cactuar\Admin\Models\EmailTemplate as Model;
use Cactuar\Admin\Models\Log;
use Cactuar\Admin\Helpers\admin;
use Cactuar\Admin\Models\Widget;
use Form;
use Auth;

trait EmailTemplateTrait
{
    public function getEmailTemplate()
    {
        $conf = $this->emailTemplateCurrent();
        $module = 'email-template';
        
        $form = adminForm::initial($module, $conf['fields']);
        $res = Model::wherePurpose($conf['key'])->first();
        if (!$res)
            $res = new Model;
        
        $selectors = []; $templates = $this->emailTemplates();
        if (count($templates) > 1) {
            foreach ($templates as $k => $v) {
                if (!array_get($v, 'label'))
                    $v['label'] = ucwords(str_replace('-', ' ', $k));
                
                $selectors[$k] = $v['label'];
            }
        }
        
        $data = [
			'label' => 'Email Template : '.array_get($conf, 'label'),
        	'menu' => $this->baseMenu($this->module(), 'email-template'),
			'form' => $form->draw($res),
			'append' => $form->widgetSource() . $this->emailTemplateAppend(),
            'description' => array_get($conf, 'description'),
            'selectors' => $selectors,
            'unique' => $conf['key'],
        ];
		
		return view('cactuar::admin.email-template', compact('data','res','conf'));
    }
    
    public function postEmailTemplate()
    {
        $conf = $this->emailTemplateCurrent();
        
        $module = 'email-template';
        $fields = $conf['fields'];
        $res = Model::wherePurpose($conf['key'])->first();
		
        if (request()->get('delete')) {
            if ($res && array_get($conf,'required') != true) {
                Widget::whereModule('email-template')->where('uniqid',$res->id)->delete();
                $res->delete();
            }
            $save = true;
        } else {
            if (!$res) {
                $res = new Model;
                $res->purpose = $conf['key'];
                $res->type = $conf['type'];
                $res->subject_admin = '';
                $res->body_admin = '';
            }
            $save = adminForm::initial($module, $fields)->save($res,null,null,null);
        }
        
        if ($save) {
			Log::write($module, 'email-template', array_get($conf, 'key'), array_get($conf, 'label'));
			return admin::redirect($this->module().'/email-template?unique='.array_get($conf, 'key'))->with('success', 'Data has been updated');
		} else {
			view()->share('warningc', ['Faied update data']);
			return $this->getEmailTemplate();
		}
    }
    
    public function emailTemplateCurrent()
    {
        $unique = request()->validated('unique','string');
        if (!is_string($unique))
            $unique = '';
        
        $templates = $this->emailTemplates();
        $keys = array_keys($templates);
        
        if (!in_array($unique, $keys))
            $unique = array_shift($keys);
        
        if (!$unique)
            abort(404);
        
        $conf = array_get($templates, $unique);
        
        $infoBody = '';
        if (is_array(array_get($conf, 'keywords'))) {
            $infoBody = '<h3 style="margin-top:5px;">Available Keywords</h3>
                <table>
                    <tr>
                        <th colspan=2 style="border-bottom:thin solid #aaa;">KEYWORDS</th>
                        <th style="border-bottom:thin solid #aaa;">DESCRIPTION</th>
                    </tr>';
            foreach (array_get($conf, 'keywords') AS $key => $val) {
                $infoBody .= '<tr><th style="padding-right:20px;">['.$key.']</td><td style="width:30px;text-align:center;">:</td><td>'.$val.'</td></tr>';
            }
            $infoBody .= '</table>';
        }
        
        $fields = [];
        
        if ($conf['type'] == 'Admin') {
            $fields['email_admin'] = [
                'label' => 'Email Addres',
                'type' => 'text',
				'info' => 'Multiple address. seperate by comma (,)',
                'attributes' => ['class' => 'required multiEmail']
            ];
        }
        
        $fields['cc'] = [
            'label' => 'CC',
            'type' => 'text',
            'info' => 'Multiple address. seperate by comma (,)',
            'attributes' => ['class' => ' multiEmail'],
        ];
        
        $fields['bcc'] = [
            'label' => 'BCC',
            'type' => 'text',
            'info' => 'Multiple address. seperate by comma (,)',
            'attributes' => ['class' => ' multiEmail'],
        ];
        
        if ($conf['type'] == 'Admin Custom' || $conf['type'] == 'Admin') {
            $fields['subject_admin'] = [
                'label' => 'Subject',
                'type' => 'text',
                'attributes' => ['class' => 'required']
            ];
            $fields['body_admin'] = [
                'label' => 'Body',
                'type' => 'textarea',
                'info' => $infoBody,
                'attributes' => ['class' => 'required ckeditor']
            ];
        } else {
            $fields['subject_end_user'] = [
                'label' => 'Subject',
                'type' => 'text',
                'multilang' => true,
                'attributes' => ['class' => 'required']
            ];
            $fields['body_end_user'] = [
                'label' => 'Body',
                'type' => 'textarea',
                'multilang' => true,
				'info' => $infoBody,
                'attributes' => ['class' => 'required ckeditor']
            ];
        }
        
        if ($conf['type'] == 'Admin' || $conf['type'] == 'Admin Custom') {
            $fields['attachment'] = [
                    'subtitle' => 'Attachments',
                    'type' => 'widget',
                    'widgets' => [
                        'attachment' => [
                            'label' => 'File',
                            'type' => 'text',
                            'attributes' => [
                                'class' => 'cfind required',
                                'cfind-type' => 'file',
                            ]
                        ]
                    ]
                ];
        } else {
            $fields['attachment-end-user'] = [
                'subtitle' => 'Attachments',
                'type' => 'widget',
                'widgets' => [
                    'attachment' => [
                        'label' => 'File',
                        'type' => 'text',
                        'multilang' => true,
                        'attributes' => [
                            'class' => 'cfind required',
                            'cfind-type' => 'file',
                        ]
                    ]
                ]
            ];
        }
        
        if (!array_get($conf, 'label'))
            $conf['label'] = ucwords(str_replace('-',' ',$unique));
        
        $conf['fields'] = $fields;
        $conf['key'] = $unique;
        return $conf;
    }
    
    public function getSendTestEmail()
    {
        $email = request()->queryValidated('email','string',true);
        $ex = explode(',',$email);
        $emails = [];
        foreach ($ex as $val)
            if (filter_var($val, FILTER_VALIDATE_EMAIL))
                array_push($emails, $val);
        
        if (count($emails) <= 0) {
            view()->share('errc', ['Please enter valid email address']);
            return $this->getEmailTemplate();
        }
        
        $unique = request()->queryValidated('unique','string',true);
        $templates = $this->emailTemplates();
        if (!array_key_exists($unique, $templates))
            abort(404);
        
        \Cactuar\Admin\Models\EmailSending::initial($unique)->to($emails)->send();
        return redirect(admin::url(admin::module().'/email-template?unique='.$unique))->with('success', 'test email has been sent');
    }
    
    public function emailTemplateAppend()
    {
        return '';
    }
    
    public function emailTemplates()
    {
        return [];
    }
    
    public function emailTemplateMainMenu($menu)
    {
        return $menu;
    }
}
