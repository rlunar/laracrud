<?php

namespace LaraCrud;

use Exception;
use LaraCrud\LaraCrudGrammar;

trait LaraCrudModel
{

    /**
     * Record Activity
     */
    use LaraCrudRecordActivity;

    /**
     * Events available to log
     * @var [type]
     */
    protected static $recordEvents = ['created','updated','deleted'];

    /**
     * Log Activity
     * 
     * @param  string                       $name
     * @param  Illuminate\Database\Eloquent $related
     * @return boolean
     */
    public function logActivity($name, $related)
    {
        if (!method_exists($related, 'recordActivity')) {
            throw new Exception("...");
        }
        
        return $related->recordActivity($name);
    }

    /**
     * Get headers from DB Schema
     * @return array
     */
    public function getHeaders()
    {
        $model = $this;
        
        $headers = \DB::table('information_schema.columns')
                    ->select(LaraCrudGrammar::getColumns())
                    ->where('table_schema', env('DB_DATABASE'))
                    ->where('table_name', $model->getTable())
                    ->get();
        
        return $this->prettyNames($this->removeUnnecessaryHeaders($headers, $model));
    }

    /**
     * Remove hidden fields from model $hidden property
     * @param  array  $headers
     * @param  Model  $model
     * @return array
     */
    protected function removeUnnecessaryHeaders($headers = array(), $model)
    {
        foreach ($headers as $index => $header) {
            if (in_array($header->column_name, $model->hidden)) {
                unset($headers[$index]);
            }
        }
        return $headers;
    }

    /**
     * Modify column name to be more UI friendly
     * @param  array  $headers
     * @return array
     */
    protected function prettyNames($headers = array())
    {
        foreach ($headers as &$header) {
            $splitted = explode(' ', str_replace('_', ' ', str_replace('_id', '', $header->column_name)));
            foreach ($splitted as &$splitword) {
                $splitword = ucfirst($splitword);
            }
            $header->title = implode(' ', $splitted);
        }

        return $headers;
    }

    /**
     * Get rules from DB Schema, this will be use on the POST & PUT request
     * @return array
     */
    public function getRules()
    {
        $headers            = $this->getHeaders();
        $rules              = array();
        $tableRelationShips = LaraCrud::getRelationShips($this);

        foreach ($headers as $key => $header) {
            if ($header->column_name !== 'id' && $header->is_nullable === 'NO') {
                $isForeignKey = LaraCrud::isForeignKey($tableRelationShips, $header->column_name);

                $rules[$header->column_name] = 'required|';

                if ($header->data_type != 'date' && !$isForeignKey) {
                    $rules[$header->column_name] .= 'max:'.(is_null($header->numeric_precision) ? $header->character_maximum_length : $header->numeric_precision).'|';
                } elseif ($header->data_type == 'date' && !$isForeignKey) {
                    $rules[$header->column_name] .= 'date|';
                }

                if ($header->column_name == 'email' && !$isForeignKey) {
                    $rules[$header->column_name] .= 'email|';
                }

                if ($isForeignKey) {
                    $rules[$header->column_name].='not_in:0|';
                }
                $rules[$header->column_name] = substr($rules[$header->column_name], 0, -1);
            }
        }

        return $rules;
    }
    
}
