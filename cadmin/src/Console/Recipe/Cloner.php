<?php namespace Cactuar\Admin\Console\Recipe;

use Illuminate\Console\Command;
use Cactuar\Admin\Helpers\helper;

class Cloner extends Command
{
    protected $signature = 'recipe:clone 
            {source-recipe : source recipe name} 
            {target-recipe : target recipe name}
            {table-name : target table name}';
    
    protected $description = 'clone recipe';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $source = $this->argument('source-recipe');
        $target = $this->argument('target-recipe');
        $table = $this->argument('table-name');
        
        $sourcePath = storage_path('recipes/'.$source.'.recipe.php');
        if (!file_exists($sourcePath))
            return $this->error('missing source file '.$sourcePath);
        $targetPath = storage_path('recipes/'.$target.'.recipe.php');
        if (file_exists($targetPath))
            return $this->error('failed create recipe, file exists '.$targetPath);
        
        $recipe = require $sourcePath;
        $recipe['table'] = $table;
        
        $q = '<?php
        
return [
'.$this->arrayWriter($recipe).'
];';
        
        $res = fopen($targetPath,'w');
        fwrite($res,$q);
        fclose($res);
        
        return $this->info('success clone recipe into '.$targetPath);
    }
    
    public static function arrayWriter($var,$deep=0)
	{
		$outs = [];
		
		foreach ($var as $k=>$v) {
			$out = str_repeat("\t",$deep);
			if (is_string($k))
				$k = '"'.$k.'"';
			if (is_string($v))
				$v = '"'.$v.'"';
			if (is_bool($v))
				$v = $v ? 'true' : 'false';
			
			$out .= $k.' => ';
			if (is_array($v))
				$out .= '['.PHP_EOL.self::arrayWriter($v,$deep+1).PHP_EOL.str_repeat("\t",$deep).']';
			else
				$out .= $v;
			$outs[] = $out;
		}
		
		return implode(','.PHP_EOL,$outs);
	}
}