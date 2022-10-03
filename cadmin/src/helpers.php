<?php

use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Models\Conf;
use Cactuar\Admin\Models\Permalink;
use Cactuar\Admin\Models\MetaData;
use Cactuar\Admin\Helpers\helper;
use Cactuar\Admin\Helpers\dataListing;

if (!function_exists('minify')) {
    function minify($type)
    {
        return new \Cactuar\Admin\Helpers\minify($type);
    }
}

if (!function_exists('widget')) {
    function widget($uniqid, $module, $key, $multiple = true)
    {
        return Widget::initial($uniqid, $module, $key, $multiple);
    }
}

if (!function_exists('widget_conf')) {
    function widget_conf($module,$key,$multiple = true)
    {
        return conf($module)->widget($key,$multiple);//widget(99,$module.'-conf',$key,$multiple);
    }
}

if (!function_exists('conf')) {
    function conf($module)
    {
        return Conf::initial($module);
    }
}

if (!function_exists('permalink')) {
    function permalink()
    {
        return Permalink::active();
    }
}

if (!function_exists('num2string')) {
	function num2string($string, $decLen = 0)
	{
		return helper::num2string($string, $decLen);
	}
}

if (!function_exists('string2num')) {
	function string2num($string)
	{
		return helper::string2num($string);
	}
}

if (!function_exists('date2string')) {
	function date2string($date, $format = 'd F Y',$empty = '')
	{
		return helper::date2string($date, $format,$empty);
	}
}

if (!function_exists('metaSet')) {
    function metaSet($uniqid,$module,$lang = null)
    {
        return MetaData::meta($uniqid,$module,$lang)->ogSet();
    }
}

if (!function_exists('dataListing')) {
    function dataListing($path)
    {
        return dataListing::html($path);
    }
}