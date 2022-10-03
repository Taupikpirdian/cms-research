<?php namespace Cactuar\Admin\Helpers;

class recaptcha
{
    public static function draw()
    {
		$hl = \App::getLocale();

        return '<script src="https://www.google.com/recaptcha/api.js?hl='.$hl.'"></script>
        <div class="g-recaptcha" data-sitekey="'.config('cadmin.cadmin.recaptcha.key').'"></div>';
    }

    public static function validate($token)
    {
        if (!$token)
            return false;

        $res =  file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.config('cadmin.cadmin.recaptcha.secret').'&response='.$token.'&remoteip='.$_SERVER['REMOTE_ADDR']);
        $result = json_decode($res, TRUE);
        if (array_get($result, 'success') == true) return true;
            return false;
    }

    public static function drawV3()
    {
        $hl = \App::getLocale();

        $path = preg_replace('/[^A-Za-z0-9]/', '_', request()->getPathInfo());
        $path = substr($path, 1, strlen($path));
        return '<script src="https://www.google.com/recaptcha/api.js?render='.config('cadmin.cadmin.recaptcha.key').'&hl='.$hl.'"></script>
            <input type="hidden" name="g-recaptcha-response" id="recaptcha">
            <script>
            grecaptcha.ready(function() {
                grecaptcha.execute("'.config('cadmin.cadmin.recaptcha.key').'", {action: "'.$path.'"}).then(function(token) {
                    if (token) {
                        $("input[name=g-recaptcha-response]").val(token);
                    }
                });
            });
        </script>';
    }

    public static function validateV3($token)
    {
		if (!$token)
            return false;

        $data   = [
            'secret' => config('cadmin.cadmin.recaptcha.secret'),
            'response' => $token,
            'remoteip' => request()->server->get('REMOTE_ADDR')
        ];
        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            ]
        ];
        $context    = stream_context_create($options);
        $res        = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
        $result     = json_decode($res, TRUE);

        if (array_get($result, 'success') == true && array_get($result, 'score') > 0.3){
            return true;
        }

        return false;
    }
}
