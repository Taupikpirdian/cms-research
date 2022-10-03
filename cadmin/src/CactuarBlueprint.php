<?php namespace Cactuar\Admin;

class CactuarBlueprint extends \Illuminate\Database\Schema\Blueprint
{
    public function translated()
    {
        $ori = $this->table;
        $this->table = $this->table.'_lang';
        
        $this->unsignedInteger('base_id');
        $this->string('lang')->index();
        $this->unique(['base_id', 'lang']);
        $this->foreign('base_id')
            ->references('id')->on($ori)
                    ->onDelete('cascade');
    }
    
    public function readable()
    {
        return $this->tinyInteger('is_read')->default(0)->index();
    }
    
    public function sortable()
    {
        return $this->integer('sort_id')->default(1);
    }
    
    public function publishable()
    {
        return $this->tinyInteger('is_active')->default(0)->index();
    }
    
    public function meta()
    { //moved into meta_datas table
        if (strpos($this->table, '_lang')) {
            $this->string('meta_title');
            $this->text('meta_keywords');
            $this->text('meta_description');  
            return;
        }
        
        else
            return $this->string('meta_image');
    }
    
    public static function schema()
    {
        $schema = \DB::connection()->getSchemaBuilder();

        $schema->blueprintResolver(function($table, $callback) {
            return new \Cactuar\Admin\CactuarBlueprint($table, $callback);
        });
        
        return $schema;
    }
}