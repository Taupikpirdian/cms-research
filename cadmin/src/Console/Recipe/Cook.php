<?php namespace Cactuar\Admin\Console\Recipe;

use Illuminate\Console\Command;
use Cactuar\Admin\Helpers\helper;

class Cook extends Command
{
    protected $signature = 'recipe:cook 
            {recipe : recipe name} 
            {--skip-migration=0 : if want to skip create migration}
            {--skip-model=0 : if want to skip create model}
            {--skip-controller=0 : if want to skip create controller}';
    
    protected $description = 'cook recipe and auto generate migration, model & controller';
    
    private $paths = [];
    private $recipe = [];
    private $feature = [];
    private $recipeName = '';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->paths = [
            'migration' => database_path('migrations'),
            'model' => app_path('Models'),
            'controller' => app_path('Http/Controllers/Admin'),
        ];
        
        foreach ($this->paths as $v)
            if (!file_exists($v))
                mkdir($v,0775,true);
    }

    public function handle()
    {
        $this->recipeName = $this->argument('recipe');
        
        $recipeFile = storage_path('recipes/'.$this->recipeName.'.recipe.php');
        if (!file_exists($recipeFile))
            return $this->error('missing recipe file '.$recipeFile);
        
        $this->recipe = require $recipeFile;
        
        //define feature
        $this->feature['sortable'] = array_get($this->recipe,'sortable') == true ? true : false;
        $this->feature['active'] = array_get($this->recipe,'active') == true ? true : false;
        $this->feature['create'] = array_get($this->recipe,'create') == true ? true : false;
        $this->feature['edit'] = array_get($this->recipe,'edit') == true ? true : false;
        $this->feature['delete'] = array_get($this->recipe,'delete') == true ? true : false;
        $this->feature['download'] = array_get($this->recipe,'download') == true ? true : false;
        $this->feature['translate'] = false;
        $this->feature['permalink'] = false;
        $this->feature['draft'] = false;
        
        foreach ($this->recipe['fields'] as $v) {
            if (array_get($v,'multilang') == true)
                $this->feature['translate'] = true;
            if (array_get($v,'type') == 'permalink')
                $this->feature['permalink'] = true;
            if (array_get($v,'draft') == true)
                $this->feature['draft'] = true;
        }
        
        if ($this->option('skip-migration') != 1)
            $this->createMigration();
        
        if ($this->option('skip-model') != 1)
            $this->createModel();
        
        if ($this->option('skip-controller') != 1)
            $this->createController();
        
        $this->nextStepInfo();
    }
    
    private function nextStepInfo()
    {
        $this->info('Next thing to do :');
        $this->info('1. recheck new-generated file');
        $this->info('2. run migration');
        $this->info('3. register menu on config/cadmin/menu.php with key name "'.$this->recipeName.'"');
    }
    
    private function createMigration()
    {
        $fName = date('Y_m_d_His').'_'.str_replace('-','_',str_replace(' ','_',$this->recipeName)).'.php';
        $className = ucwords(helper::dash2camel($this->recipeName));
        $migration = $this->paths['migration'].'/'.$fName;
        if (file_exists($migration))
            return $this->error('failed create migration, file exists '.$migration);
        
        $main = $trans = [];
        foreach (array_get($this->recipe,'fields') as $k=>$v) {
            if (in_array(array_get($v,'type'),['widget','permalink','meta']))
                continue;
            if (array_get($v, 'multilang') == true)
                $trans[$k] = $v;
            else
                $main[$k] = $v;
        }
        if (array_get($this->recipe,'sortable') == true)
            $main['sort_id'] = ['type' => 'number'];
        if (array_get($this->recipe,'active') == true)
            $main['is_active'] = ['type' => 'number'];
        
        $q = '<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Cactuar\Admin\CactuarBlueprint;

class '.$className.' extends Migration
{
    public function up()
    {';
        if (!empty($main)) {
            $q .= '
        CactuarBlueprint::schema()->create("'.$this->recipe['table'].'",function(CactuarBlueprint $table) {
            $table->increments("id");';
            foreach($main as $k=>$v) {
                if ($v['type'] == 'date')
                    $q .= '
            $table->date("'.$k.'");';
                if ($v['type'] == 'text')
                    $q .= '
            $table->string("'.$k.'");';
                if ($v['type'] == 'textarea')
                    $q .= '
            $table->text("'.$k.'");';
                if ($v['type'] == 'number' || $v['type'] == 'select')
                    $q .= '
            $table->integer("'.$k.'");';
            }
        
            $q .= '
            $table->timestamps();
        });';    
        }
        
        if (!empty($trans)) {
            $q .= '
        CactuarBlueprint::schema()->create("'.$this->recipe['table'].'",function(CactuarBlueprint $table) {
            $table->translated();';
            foreach($trans as $k=>$v) {
                if ($v['type'] == 'date')
                    $q .= '
            $table->date("'.$k.'");';
                if ($v['type'] == 'text')
                    $q .= '
            $table->string("'.$k.'");';
                if ($v['type'] == 'textarea')
                    $q .= '
            $table->text("'.$k.'");';
                if ($v['type'] == 'int')
                    $q .= '
            $table->integer("'.$k.'");';
            }
            $q .= '
        });';
        }
        
        $q .= '
    }
    
    public function down()
    {';
        
        if (!empty($trans))
            $q .= '
        Schema::dropIfExists("'.$this->recipe['table'].'_lang");';
        
        if (!empty($main))
        $q .= '
        Schema::dropIfExists("'.$this->recipe['table'].'");';
        
        $q .= '
    }
}';
        
        $res = fopen($migration,'w');
        fwrite($res,$q);
        fclose($res);
        $this->info('created migration file '.$migration);
        
    }
    
    private function createModel()
    {
        $className = ucwords(helper::dash2camel($this->recipeName));
        $model = $this->paths['model'].'/'.$className.'.php';
        if (file_exists($model)) 
            return $this->error('failed create model, file exists '.$model);
        
        $traitPaths = $traits = [];
        if ($this->feature['permalink'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Models\PermalinkTrait';
        if ($this->feature['translate'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Models\TranslateTrait';
        if ($this->feature['sortable'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Models\SortableTrait';
        
        foreach($traitPaths as $k=>$v)
            $traits[$k] = basename($v);
        
        $q = '<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;';
        foreach($traitPaths as $v)
            $q .= '
use '.$v.';';
        
        $q .= '
        
class '.$className.' extends Model
{';
        if (!empty($traits))
            $q .= '
    use '.implode(', ',$traits).';
    ';
        
        $q .= '
    protected $table = "'.$this->recipe['table'].'";
    public $module = "'.$this->recipeName.'";';
        if ($this->feature['sortable'] && array_get($this->recipe, 'sortableParent'))
            $q .= '
    public $sortParent = "'.$this->recipe['sortableParent'].'";';
        
        $q .= '
        
}';
        $res = fopen($model,'w');
        fwrite($res,$q);
        fclose($res);
        
        $this->info('created model '.$model);
    }
    
    private function createController()
    {
        $className = ucwords(helper::dash2camel($this->recipeName));
        $controller = $this->paths['controller'].'/'.$className.'Controller.php';
        if (file_exists($controller))
            return $this->error('failed create controller, file exists '.$controller);
        
        $traitPaths = $traits = ['Cactuar\Admin\Traits\Controllers\BaseTrait','Cactuar\Admin\Traits\Controllers\ListingTrait'];
        if ($this->feature['create'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\CreateTrait';
        if ($this->feature['edit'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\EditTrait';
        if ($this->feature['delete'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\DeleteTrait';
        if ($this->feature['active'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\PublishTrait';
        if ($this->feature['download'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\DownloadTrait';
        if ($this->feature['draft'])
            $traitPaths[] = 'Cactuar\Admin\Traits\Controllers\DraftTrait';
        
        foreach ($traitPaths as $k=>$v) {
            $ex = explode('\\',$v);
            $name = end($ex);
            $traits[$k] = $name;
        }
        
        $fields = [];
        foreach ($this->recipe['fields'] as $k=>$v) {
            if (!is_array(array_get($v,'attributes')))
                $v['attributes'] = [];
            if (!array_get($v,'attributes.class'))
                $v['attributes']['class'] = '';
            
            if ($v['type'] == 'date') {
                $v['type'] = 'text';
                $v['attributes']['class'] .= ' datepicker';  
            } 
            
            if ($v['type'] == 'number') {
                $v['type'] = 'text';
                $v['attributes']['class'] .= ' numeric';
            }
            
            $fields[$k] = $v;
        }
        
        $q = '<?php namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\\'.$className.';';
        
        foreach ($traitPaths as $v)
            $q .= '
use '.$v.';';
        
        $q .= '
        
class '.$className.'Controller extends Controller
{';
        if (!empty($traits))
            $q .= '
    use '.implode(', ',$traits).';
    ';
        
        $q .= '
    public function listingRes($type = "")
    {
        return '.$className.'::'.(($this->feature['translate'] == true) ? 'translate' : 'select').'();
    }
    ';
        
        if (is_array(array_get($this->recipe,'listing-fields')))
            $q .= '
    public function listingFields($type = "")
    {
        return [
'.self::arrayWriter($this->recipe['listing-fields'],2).'
        ];
    }
    ';
        if (is_array(array_get($this->recipe,'listing-searchs')))
            $q .= '
    public function listingSearchs()
    {
        return [
'.self::arrayWriter($this->recipe['listing-searchs'], 2).'        
        ];
    }
    ';
        
        if ($this->feature['create'] || $this->feature['edit'] || $this->feature['delete'])
            $q .= '
    public function formRes($type = "")
    {
        return new '.$className.';
    }
    '; 
        
        $q .= '
    public function formFields($type = "")
    {
        return [
'.self::arrayWriter($fields,2).'        
        ];
    }   
    ';
        $q .= '
        
}';
        
        $res = fopen($controller,'w');
        fwrite($res,$q);
        fclose($res);
        
        $this->info('created controller '.$controller);
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


