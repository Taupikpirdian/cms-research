<?php namespace Cactuar\Admin\Models;

use Illuminate\Database\Eloquent\Model;
use Cactuar\Admin\Helpers\lang;
use Cactuar\Admin\Models\MetaData;
use Cactuar\Admin\Models\Widget;
use Cactuar\Admin\Helpers\og;
use Cactuar\Admin\Helpers\media;
use Schema;
use DB;

class Permalink extends Model
{
    protected $table = 'permalinks';
    private static $active = null;
    
    public static function active()
    {
        if (!is_null(self::$active))
            return self::$active;
        
        $res = self::overwrite(implode('/',request()->segments()));
        if ($res) {
            self::$active = $res;
            MetaData::meta($res->uniqid,$res->module,$res->lang)->ogSet();
            og::set('canonical',self::$active->permalink);
        }
        
        return $res;
    }
    
    public static function overwrite($permalink)
    {
        if (!\Schema::hasTable('permalinks'))
            return null;
        $res = self::wherePermalink($permalink)->first();
        if (!$res)
            return null;
        
        lang::active($res->lang);
        foreach ($res->permalinks as $k => $v)
            lang::setUrl($k,url($v));
        
        return $res;
    }
    
    public static function permalink($module, $uniqid, $lang = null)
    {
        if (!$uniqid)
            return '';
        
        if (is_null($lang))
            $lang = lang::active();
        
        $res = self::whereModule($module)->whereUniqid($uniqid)->whereLang($lang)->first();
        if (!$res)
            return '';
        return $res->permalink;
    }
    
    public function getPermalinksAttribute()
    {
        return self::whereModule($this->module)->whereUniqid($this->uniqid)->pluck('permalink', 'lang')->all();
    }

    public function compileMeta($res)
    {
		
        $cfindTrueValue = function($val) {
            if (media::initial($val)->path) {
                return media::initial($val)->url.' '.media::initial($val)->getAlt($this->lang);
            }
            return strip_tags($val);
        };

        $searchable = [];
	
        if (Schema::hasTable('meta_datas')) {
            $permalinkMeta = MetaData::whereModule($this->module)->whereUniqid($this->uniqid)->whereLang($this->lang)->first();
            if ($permalinkMeta && $permalinkMeta->id) {
                $this->label = $permalinkMeta->meta_title;
                $this->description = $permalinkMeta->meta_description;
				$searchable[] = $permalinkMeta->meta_title;
				$searchable[] = $permalinkMeta->meta_description;
				$searchable[] = $permalinkMeta->meta_keywords;
				$searchable[] = $cfindTrueValue($permalinkMeta->meta_image);
            }
        }

        foreach (['name','title','label'] as $k) {
            if ($res->transTable && Schema::hasTable($res->transTable) && Schema::hasColumn($res->transTable, $k)) {
                $this->label = $res->translated($k,$this->lang);
            } else if (Schema::hasColumn($res->getTable(),$k))
                $this->label = $res->{$k};
        }

        if (Schema::hasColumn($res->getTable(),'is_active'))
            $this->is_active = $res->is_active ? $res->is_active : 0;
		else
			$this->is_active = 1;
			
        foreach ($res->getAttributes() as $k=>$v) {
            if (in_array($k,['id','sort_id','is_active','created_at','updated_at']) || gettype($v) != 'string' )
                continue;
            $searchable[] = $cfindTrueValue($v);
        }
        if ($res->transTable && Schema::hasTable($res->transTable)) {
            $translate = DB::table($res->transTable)->whereBaseId($res->id)->whereLang($this->lang)->first();
            if ($translate && $translate->base_id) {
                foreach(json_decode(json_encode($translate),true) as $k=>$v) {
                    if (in_array($k,['base_id','lang','searchable']))
                        continue;
                    $searchable[] = $cfindTrueValue($v);
                }
            }
        }

        if (Schema::hasTable('widgets')) {
            foreach(Widget::whereModule($this->module)->whereUniqid($this->uniqid)->get() as $w) {
                foreach (WidgetDetail::whereWidgetId($w->id)->whereIn('lang',[null,'',$this->lang])->get() as $wd)
                    $searchable[] = $cfindTrueValue($wd->val);
            }
        }

        $this->searchable = implode(',', array_filter($searchable));
        
        $this->save();
    }

    public static function sitemapXML($statics = [], $freq = 'monthly', $priority = '0.8')
    {
        $urls = [];

        foreach(self::whereIsActive(1)->get() as $p) {
            $urls[] = '
    <url>
        <loc>'.url($p->permalink).'</loc>
        <lastmod>'.$p->updated_at->format('Y-m-d').'</lastmod>
        <changefreq>'.$freq.'</changefreq>
        <priority>'.$priority.'</priority>
    </url>';
        }

        $default = config('cadmin.lang.default');
        foreach($statics as $v) {
            foreach (\Cactuar\Admin\Helpers\lang::codes() as $lang) {
                $urls[] = '
                <url>
                    <loc>'.url(($lang == $default ? '' : $lang.'/').array_get($v, 'permalink')).'</loc>
                    <lastmod>'.(array_get($v, 'date') ? $v['date'] : $p->updated_at->format('Y-m-d')).'</lastmod>
                    <changefreq>'.((array_get($v, 'freq')) ? $v['freq'] : $freq).'</changefreq>
                    <priority>'.((array_get($v, 'priority')) ? $v['priority'] : $priority).'</priority>
                </url>';
            }
        }

        header('Content-type: text/xml');
        return '<?xml version="1.0" encoding="UTF-8" ?>
<urlset xmlns="http://www.google.com/schemas/sitemap/0.84" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd">'.implode('',$urls).'
</urlset>';
    }
}