<?php namespace Cactuar\Admin\Models;

use Cactuar\Admin\Models\Conf;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\EmailTemplate;
use Cactuar\Admin\Helpers\media;

class EmailSending
{
    private $type;
    private $param = [];
    
    public static function initial($type)
    {
        return new self($type);
    }
    
    public function __construct($type)
    {
        $this->type = $type;
    }
    
    private function param($k,$v)
    {
        if (!array_key_exists($k,$this->param))
            $this->param[$k] = [];
        
        if (is_array($v)) {
            foreach ($v as $vv)
                array_push($this->param[$k],$vv);
        } else {
            array_push($this->param[$k], $v);
        }
        return $this;
    }
    
    public function to($param)
    {
        return $this->param('to',$param);
    }
    
    public function cc($param)
    {
        return $this->param('cc',$param);
    }
    
    public function bcc($param)
    {
        return $this->param('bcc',$param);
    }
    
    public function attachment($param)
    {
        return $this->param('attachment',$param);
    }
    
    public function origin($origin,$originId)
    {
        $this->param['origin'] = $origin;
        $this->param['originid'] = $originId;
        return $this;
    }
    
    public function from($from,$name)
    {
        $this->param['from'] = $from;
        $this->param['fromname'] = $name;
        return $this;
    }
    
    public function data(array $data)
    {
        $this->param['data'] = $data;
        return $this;
    }
    
    public function send()
    {
        list($subject,$body,$to,$cc,$bcc,$from,$fromName,$files) = $this->options();
        
        if (!$to || !$subject || !$body)
			return;

         //manipulate cfind url
        $url = asset('');
        $url = explode('/',str_replace('//','',$url));
        array_shift($url);
        $cfindPath = preg_replace('#/+#','/','/'.implode('/',$url).'/'.config('cadmin.media.sourcepath'));
        $cfindDomain = (request()->secure() ? 'https://' : 'http://').request()->server('HTTP_HOST').$cfindPath;
        
        $body = str_ireplace($cfindPath,$cfindDomain,$body);

        \Mail::send('email-blank', ['content' => $body], function ($message) use ($subject, $to, $cc, $bcc, $from, $fromName, $files) {
            $message->to($to);
            if (!empty($cc))
                $message->cc($cc);
            
            if (!empty($bcc))
                $message->bcc($bcc);
            
            $message->subject($subject);
            
            $message->from($from, $fromName);
            
            foreach ($files AS $f)
                $message->attach($f);
        });
        
        return true;
    }
    
    private function options()
    {
        
        $param = $this->param;
        $type = $this->type;
        
        $template = EmailTemplate::wherePurpose($type)->translate()->first();
        if (!$template)
            return true;
        
        $template->body = $template->body_admin;
        $template->subject = $template->subject_admin;
        
        if ($template->type == 'End User') {
            $template->body = $template->body_end_user;
            $template->subject = $template->subject_end_user;
        }
        
        if (is_array(array_get($param,'data'))) {
            foreach ($param['data'] as $k=>$v) {
                $template->body = str_ireplace('['.$k.']',$v,$template->body);
            }
        }
        
        $body = $template->body;
        $subject = $template->subject;
        
        $to = $template->email_admin ? explode(',',str_replace(' ','',$template->email_admin)) : [];
        $cc = $template->cc ? explode(',',str_replace(' ','',$template->cc)) : [];
        $bcc = $template->bcc ? explode(',',str_replace(' ','',$template->bcc)) : [];
        
        if (is_array(array_get($param,'to')))
            foreach ($param['to']  as $v)
                array_push($to,$v);
        
        if (is_array(array_get($param,'cc')))
            foreach ($param['cc']  as $v)
                array_push($cc,$v);
        
        if (is_array(array_get($param,'bcc')))
            foreach ($param['bcc'] as $v)
                array_push($bcc,$v);
        
        $files = [];
        if (is_array(array_get($param,'attachment')))
            foreach ($param['attachment'] as $v)
                if (file_exists($v))
                    array_push($files,$v);
        
        if (strtolower(trim($template->type)) == 'end user') {
            foreach (Widget::whereUniqid($template->id)->whereModule('email-template')->where('key', 'attachment-end-user')->get() as $v) {
                $file = $v->translated('attachment');
                if ($file && file_exists(media::source($file)))
                    array_push($files, media::source($file));
            }
        } else {
            foreach (Widget::whereUniqid($template->id)->whereModule('email-template')->where('key', 'attachment')->get() as $v) {
                $file = $v->value('attachment');
                if ($file && file_exists(media::source($file)))
                    array_push($files, media::source($file));
            }
        }
        
        if (empty($to) || !$subject || !$body)
            return false;
        
        $conf = Conf::initial('site-setting');

        $from = array_get($this->param,'from');
        $fromName = array_get($this->param,'fromname');
        
        if (!$from)
            $from = $conf->emailFrom;
        if (!$fromName)
            $fromName = $conf->emailFromName;

        if (!$from)
            $from = 'no-reply@test.com';
        if (!$fromName)
            $fromName = 'No Reply';
        
        return [$subject,$body,$to,$cc,$bcc,$from,$fromName,$files];
    }
}