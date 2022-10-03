<?php
namespace Cactuar\Admin\Helpers;
use App;

class helper 
{
    
    public static function string2num($string)
    {
        if (App::getLocale() == 'id') {
            return (float) str_replace(',', '.', str_replace('.', '', $string));   
        }
        
        return (float) str_replace(',', '', $string);
    }
    
    public static function num2string($string, $decLen = 0)
    {
		if (!is_numeric($string))
			return $string;
		
        if (App::getLocale() == 'id') 
            return number_format($string, $decLen, ',', '.');
        
        return number_format($string, $decLen);
    }
    
    public static function date2string($date, $format = 'd F Y',$empty = '')
    {
        if (substr($date, 0, strlen('0000-00-00')) == '0000-00-00' || strlen($date) < 1)
            return $empty;
        
        $time = date_create($date);
        if (App::getLocale() == 'en')
            return date_format($time,$format);
        
        $month = [
            'id' =>  ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei',
                      '06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember']
        ];
        
        $locale = App::getLocale();
        $format = str_replace('M', '%%', $format);
        $format = str_replace('F', '$$', $format);
        
        $out = date_format($time,$format);
        $out = str_replace('%%',substr(array_get($month, $locale.'.'.date_format($time,'m')),0,3),$out);
        $out = str_replace('$$',array_get($month, $locale.'.'.date_format($time,'m')),$out);
        return $out;
    }
    
    public static function csv($data, $name)
    {
        $callback = function() use($data) {
            $df = fopen("php://output", 'w');
            $sep = config('cadmin.cadmin.csv-separator');
            if (!in_array($sep,[';',',']))
                $sep = ',';
            foreach ($data as $row) {
                foreach ((array) $row AS $k => $s) {
                    $row[$k] = str_replace("\n", ':', str_replace("\r\n", ':', $s));
                }
                fputcsv($df, (array) $row, $sep);
            }
            fclose($df);
        };

		$now = gmdate("D, d M Y H:i:s");
        $headers = [
            'Content-type'=>'text/plain',
            'Content-Disposition'=>sprintf('attachment; filename="%s"', $name),
            'Expires' => 'Tue, 03 Jul 2001 06:00:00 GMT',
            'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
            'Last-Modified' => $now.' GMT',
            'Content-Type' => 'application/force-download',
            'Content-Type' => 'application/octet-stream',
            'Content-Type' => 'application/download',
            'Content-Disposition' => 'attachment;filename='.$name."-".uniqid().'.csv',
		    'Content-Transfer-Encoding' => 'binary'
        ];
        return \Response::stream($callback, 200, $headers);
	}
    
    public static function tel($str, $anchor = 'tel:')
    {
        if (!$str) return '';
        if (in_array($anchor, ['tel:', 'fax:']))
            $str = str_replace('+', '', str_replace('-', '', $str));
        
        $data = explode(',', $str);
        
        $out = [];
        foreach ($data AS $item) {
            array_push($out, '<a href="'.$anchor.str_replace(' ','', $item).'">'.htmlspecialchars(trim($item)).'</a>');
        }
        
        return $out;
    }
    
    public static function folder($path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0775, true);
        }
        
        if (!is_dir($path))
            throw new Exception($path.' exits and not writable');
        
        return $path;
    }
    
    public static function newFile($path, $ext) 
    {
        $fname = '';
        while (empty($fname) || file_exists($path.'/'.$fname)) {
            $fname = sha1(uniqid().rand(10000, 20000).rand(10000, 20000).rand(10000, 20000)).'.'.$ext;
        }
        
        return $fname;
    }
    
	public static function newToken($tb,$field = 'token')
	{
		$token = '';
		while (empty($token) || \DB::table($tb)->where($field,$token)->count() > 0) {
			$token = sha1(uniqid().rand(10000, 20000).rand(10000, 20000).rand(10000, 20000));
		}
		
		return $token;
	}
	
	public static function randcode($n_string=0, $type = 'num') {
		
		$chars_alpha	=	"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";		
		$chars_num		= 	"0123456789";
		
		$chars = '';
		if ($type == 'num') { //numeric type
			$chars = $chars_num;
		}
		if ($type == 'alpha') { //alphabet type
			$chars = $chars_alpha;
		}
		if ($type == 'alnum') { //aphabet + numeric type
			$chars = $chars_num.$chars_alpha;
		}
		
		if (!$chars) return '';
		
		$arr_chars	=	str_split($chars);
		$n_chars	=	count($arr_chars);
		
		$result		=	'';
		for($ii=0; $ii < $n_string; $ii++){
			$index			=	rand(0,($n_chars-1));
			$current_char	=	$arr_chars[$index];
			$result			.=	$current_char;
		}
		
		return $result;
	}
    
    public static function camel2dashed($str) 
    {
        if (strpos($str, '-') !== false)
            return $str;
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $str));
    }
    
    public static function dash2camel($str)
    {
        if (strpos($str, '-') === false)
            return $str;
        return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $str))));
    }
    
    public static function array_insert(&$array,$values,$offset) {
        $array = array_slice($array, 0, $offset, true) + $values + array_slice($array, $offset, NULL, true);  
    }
    
    public static function formatBytes($size, $precision = 2)
	{
		$base = log($size, 1024);
		$suffixes = array('', 'KB', 'MB', 'GB', 'TB');   

		return self::num2string(round(pow(1024, $base - floor($base)), $precision), 2) . $suffixes[floor($base)];
	}
    
    public static function youtubeEmbedURL($url)
    {
        return 'https://www.youtube.com/embed/'.self::youtubeEmbedCode($url).'?rel=0&amp;showinfo=0';
    }
    
    public static function youtubeEmbedCode($url)
    {
        preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+(?=\?)|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches);
        return $matches[0];
    }
    
    public static function ipInRange( $ip, $range = '') 
    {
        if (!$range)
            $range = $ip;
        if ( strpos( $range, '/' ) == false ) {
            $range .= '/32';
        }
        // $range is in IP/CIDR format eg 127.0.0.1/24
        list( $range, $netmask ) = explode( '/', $range, 2 );
        $range_decimal = ip2long( $range );
        $ip_decimal = ip2long( $ip );
        $wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
    }
	
	public static function terbilang($x) {
        $x = abs($x);
        $angka = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $temp = "";
		
        if ($x <12) {
            $temp = " ". $angka[$x];
        } else if ($x <20) {
            $temp = self::terbilang($x - 10). " belas";
        } else if ($x <100) {
            $temp = self::terbilang($x/10)." puluh". self::terbilang($x % 10);
        } else if ($x <200) {
            $temp = " seratus" . self::terbilang($x - 100);
        } else if ($x <1000) {
            $temp = self::terbilang($x/100) . " ratus" . self::terbilang($x % 100);
        } else if ($x <2000) {
            $temp = " seribu" . self::terbilang($x - 1000);
        } else if ($x <1000000) {
            $temp = self::terbilang($x/1000) . " ribu" . self::terbilang($x % 1000);
        } else if ($x <1000000000) {
            $temp = self::terbilang($x/1000000) . " juta" . self::terbilang($x % 1000000);
        } else if ($x <1000000000000) {
            $temp = self::terbilang($x/1000000000) . " milyar" . self::terbilang(fmod($x,1000000000));
        } else if ($x <1000000000000000) {
            $temp = self::terbilang($x/1000000000000) . " trilyun" . self::terbilang(fmod($x,1000000000000));
        }     
        
        return $temp;
    }
}
?>