<?php namespace Cactuar\Admin\Console\Recipe;

use Illuminate\Console\Command;
use Cactuar\Admin\Helpers\helper;

class Creator extends Command
{
    protected $signature = 'recipe:create 
            {recipe : recipe name} 
            {table-name : table name}';
    
    protected $description = 'create initial recipe';
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function handle()
    {
        $recipe = $this->argument('recipe');
        $table = $this->argument('table-name');
        if (!file_exists(storage_path('recipes')))
            mkdir(storage_path('recipes',0775,true));
            
        $path = storage_path('recipes/'.$recipe.'.recipe.php');
        if (file_exists($path))
            return $this->error('failed create recipe, file exists '.$path);
        
        $q = "<?php

return [
    'table' => '".$table."',
    'active' => true,
    'create' => true,
    'edit' => true,
    'delete' => true,
    'listing-fields' => ['label'=>'Label'],
    'listing-searchs' => ['label'],
    'fields' => [
        'label' => [
            'type' => 'text',
            'label' => 'Label',
            'multilang' => true,
            'attributes' => [
                'class' => 'required',
            ]
        ],
    ]
];";
        
        $res = fopen($path,'w');
        fwrite($res,$q);
        fclose($res);
        
        return $this->info('success create recipe '.$path);
    }
}