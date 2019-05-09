<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveSoftDeletes extends Migration
{
    const SOFT_DELETED_ARRAY = [
        'custom_relations',
        'custom_copy_columns',
        'custom_copies',
        'custom_view_sorts',
        'custom_view_summaries',
        'custom_view_filters',
        'custom_view_columns',
        'custom_views',
        'custom_form_columns',
        'custom_form_blocks',
        'custom_forms',
        'custom_columns',
        'custom_tables',
        'dashboard_boxes',
        'dashboards',
        'roles',
        'user_settings',
        'login_users',
        'plugins',
        'notifies',
        'systems',
    ];

    const ADD_INDEX_TABLES = [
        'plugins' => 'plugin_name',
        'roles' => 'role_name',
        'dashboards' => 'dashboard_name',
    ];


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // add index
        $this->addIndex();

        $this->dropExmTables();
        
        // hard delete if already deleted record
        foreach(static::SOFT_DELETED_ARRAY as $table_name){
            $this->deleteRecord($table_name);
        }

        // get all deleted_at, deleted_user_id's column
        $tables = \DB::select('SHOW TABLES');
        foreach ($tables as $table) {
            $this->dropDeletedRecord($table);

            $this->dropSuuidUnique($table);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (static::ADD_INDEX_TABLES as $table_name => $column_name) {
            Schema::table($table_name, function (Blueprint $table) use($column_name) {
                $table->dropIndex([ $column_name]);
            });
        }
    }

    /**
     * drop custom table's table
     */
    protected function dropExmTables(){
        if(!Schema::hasColumn('custom_tables', 'deleted_at')){
            return;
        }

        foreach (DB::table('custom_tables')->whereNull('deleted_at')->get() as $value) {
            // drop deleted table, so don't call getDBTableName function
            Schema::dropIfExists('exm__' . $value->suuid);
        }
    }

    /**
     * hard delete 
     */
    protected function deleteRecord($table_name){
        if(!Schema::hasColumn($table_name, 'deleted_at')){
            return;
        }

        $deleted = \DB::delete("delete from $table_name WHERE deleted_at IS NOT NULL");
    }

    /**
     * add key's index
     */
    protected function addIndex(){
        
        foreach (static::ADD_INDEX_TABLES as $table_name => $column_name) {
            $columns = \DB::select("SHOW INDEX FROM $table_name WHERE non_unique = 1 AND column_name = '$column_name'");

            if(count($columns) > 0){
                continue;
            }

            Schema::table($table_name, function (Blueprint $t) use($column_name) {
                $t->index([$column_name]);
            });
        }
    }
    /**
     * drop deleted record
     */
    protected function dropDeletedRecord($table){
        foreach ($table as $key => $name) {
            if (stripos($name, 'exm__') === 0 || $name == 'custom_values') {
                continue;
            }

            $columns = \DB::select("SHOW COLUMNS FROM $name WHERE field IN ('deleted_at', 'deleted_user_id')");

            if(count($columns) == 0){
                continue;
            }

            foreach($columns as $column){
                $field = $column->field;
                
                Schema::table($name, function (Blueprint $t) use($field) {
                    $t->dropColumn($field);
                });
            }
        }
    }
    
    /**
     * drop deleted record
     */
    protected function dropSuuidUnique($table){
        foreach ($table as $key => $name) {
            $columns = \DB::select("SHOW INDEX FROM $name WHERE non_unique = 0 AND column_name = 'suuid'");

            if(count($columns) == 0){
                continue;
            }

            foreach($columns as $column){
                $keyName = $column->Key_name;
                
                Schema::table($name, function (Blueprint $t) use($keyName, $name) {
                    $t->dropUnique($keyName);

                    if (stripos($name, 'exm__') === 0 || $name == 'custom_values') {
                        $t->index(['suuid'], 'custom_values_suuid_index');
                    }else{
                        $t->index(['suuid']);
                    }
                });
            }
        }
    }
}
