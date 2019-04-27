<?php
/**
 * Created by PhpStorm.
 * User: kchapple
 * Date: 7/5/18
 * Time: 2:38 PM
 */

namespace CareSet\Zermelo\Models;

use Illuminate\Support\Facades\DB;

class AbstractGenerator
{
    protected $cache = null;

    protected $_full_table = null;

    protected $_Table = null;
    protected $_filters = [];

    public function __construct( DatabaseCache $cache )
    {
        $this->cache = $cache;
    }

    public function addFilter(array $filters)
    {
        foreach($filters as $field=>$value)
        {
            if($field == '_')
            {
                $fields = ZermeloDatabase::getTableColumnDefinition( $this->cache->getTableName(), zermelo_cache_db() );
                $this->cache->getTable()->where(function($q) use($fields,$value)
                {
                    foreach ($fields as $field) {
                        $field_name = $field['Name'];
                        $q->orWhere($field_name, 'LIKE', '%' . $value . '%');
                    }
                });
            } else
            {
                $this->cache->getTable()->Where($field,'LIKE','%'.$value.'%');
            }
        }
    }

    public function orderBy(array $orders)
    {
        foreach ($orders as $key=>$direction) {
            $this->cache->getTable()->orderBy($key, $direction);
        }
    }

    public function cacheTo($destination_database, $destination_table)
    {
        $full_table = "{$destination_database}.{$destination_table}";

        $CacheQuery = clone $this->_Table;
        $sql = $CacheQuery->select("*")->toSql();
        $params = $CacheQuery->getBindings();

        DB::statement("DROP TABLE IF EXISTS {$full_table}");
        DB::statement("CREATE TEMPORARY TABLE {$full_table} AS {$sql};",$params);

        return true;
    }

}