<?php

namespace LaraCrud;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

abstract class LaraCrud
{
    
    public static function getDBTableName(Model $model)
    {
        return str_replace('\\', '', Str::snake(Str::plural(class_basename($model))));
    }

    public static function getRelationShips(Model $model)
    {
        return \DB::table('information_schema.key_column_usage')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', $model->getTable())
            ->whereNotIn('COLUMN_NAME', array('id'))
            ->get();
    }

    public static function isForeignKey($relationships = array(), $column = '')
    {
        foreach ($relationships as $relationship) {
            if ($relationship->COLUMN_NAME == $column) {
                return $relationship;
            }
        }

        return false;
    }

    public static function getForeignData($foreignKey, $id = null)
    {
        $builder = \DB::table($foreignKey->REFERENCED_TABLE_NAME)
                    ->select($foreignKey->REFERENCED_COLUMN_NAME, 'name')
                    ->where('deleted_at', null);

        if (!is_null($id)) {
            $builder->where($foreignKey->REFERENCED_COLUMN_NAME, '=', $id);
        }

        return $builder->get();
    }

    public static function displayForeignLinks(Model $model)
    {
        $headers       = $model->getHeaders();
        $relationships = self::getRelationShips($model);
        $data          = $model->get()->toArray();

        if (empty($relationships)) {
            return $data;
        }

        foreach ($data as &$row) {
            foreach ($relationships as $relationship) {
                if (in_array($relationship->COLUMN_NAME, array_keys($row))) {
                    $foreignData = self::getForeignData($relationship, $row[$relationship->COLUMN_NAME]);
                    $row[$relationship->COLUMN_NAME] = $foreignData[0]->name;
                }
            }
        }

        return $data;
    }
}
