<?php

namespace LaraCrud;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use LaraCrud\LaraCrud;

abstract class LaraCrud
{
    /**
     * Get Table name base on model name
     * 
     * @param  Model  $model
     * @return string
     */
    public static function getDBTableName(Model $model)
    {
        return str_replace('\\', '', Str::snake(Str::plural(class_basename($model))));
    }

    /**
     * Search for relationships on the info schema of database
     * 
     * @param  Model  $model
     * @return Illuminate\Database\Eloquent\Collection
     */
    public static function getRelationShips(Model $model)
    {
        return \DB::table('information_schema.key_column_usage')
            ->where('TABLE_SCHEMA', env('DB_DATABASE'))
            ->where('TABLE_NAME', $model->getTable())
            ->where('CONSTRAINT_NAME', 'NOT LIKE', '%_unique')
            ->whereNotIn('COLUMN_NAME', array('id'))
            ->get();
    }

    /**
     * Validate if given column is a foreign key
     * 
     * @param  array   $relationships
     * @param  string  $column
     * @return boolean
     */
    public static function isForeignKey($relationships = array(), $column = '')
    {
        foreach ($relationships as $relationship) {
            if ($relationship->COLUMN_NAME == $column) {
                return $relationship;
            }
        }

        return false;
    }

    /**
     * Display data for model and foreign data if has relationships.
     * 
     * @param  Model  $model [description]
     * @return array
     */
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
                    if (!empty($foreignData)) {
                        $row[$relationship->COLUMN_NAME] = $foreignData[0]->name;                        
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Get Foreign Data for relationships.
     * 
     * @param  StdObj   $foreignKey
     * @param  mixed    $id
     * @return Illuminate\Database\Eloquent\Collection
     */
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

    /**
     * Get html inputs from DB Schema
     * 
     * @param  array   $values
     * @param  boolean $readonly
     * @return array
     */
    public static function getInputs(Model $model, $values = array(), $readonly = false)
    {
        $tableRelationShips = self::getRelationShips($model);

        $headers          = $model->getHeaders();
        $inputs           = array();

        foreach ($headers as $header) {
            if ($header->column_name === 'id') {
                continue;
            }

            if (!empty($tableRelationShips) && $foreignKey = self::isForeignKey($tableRelationShips, $header->column_name)) {
                $inputs[] = self::getDropDown($header, $values, $readonly, self::getForeignData($foreignKey));
                continue;
            }

            $inputs[] = self::getInput($header, $values, $readonly);
        }
        
        return $inputs;
    }

    /**
     * Get input according to the type of column
     * 
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    private static function getInput($header, $values = array(), $readonly = false)
    {
        switch ($header->data_type) {
            case 'int':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'bigint':
            case 'double':
            case 'float':
            case 'decimal':
                return self::getNumericInput($header, $values, $readonly);
                break;
            case 'varchar':
                return self::getVarcharInput($header, $values, $readonly);
                break;
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return self::getTextInput($header, $values, $readonly);
                break;
            case 'date':
                return self::getDateInput($header, $values, $readonly);
                break;
            case 'datetime':
            case 'timestamp':
                return self::getTimestampInput($header, $values, $readonly);
                break;
            case 'enum':
                return self::getEnumInput($header, $values, $readonly);
                break;
        }
    }

    /**
     * Return value for input if it's set on the values array
     * 
     * @param  StdObj $header
     * @param  array  $values
     * @return mixed
     */
    private static function getValue($header, $values = array())
    {
        return !empty($values) && isset($values->{$header->column_name}) ? $values->{$header->column_name} : '';
    }

    /**
     * Return if input has readonly capability
     * 
     * @param  boolean $readonly
     * @return string
     */
    private static function getReadOnly($readonly = false)
    {
        return $readonly ? 'readonly="true"' : '';
    }

    /**
     * Return if input has disabled capability
     *
     * @param boolean $disabled
     * @return string
     */
    private static function getDisabled($disabled = false)
    {
        return $disabled ? 'disabled' : '';
    }

    /**
     * Return if input is required
     * 
     * @param  StdObj $header
     * @return string
     */
    private static function getRequired($header)
    {
        return $header->is_nullable == 'NO' ? 'required="required"' : '';
    }

    /**
     * Build Numeric field
     * 
     * @param  StdObj  $header
     * @param  array   $values]
     * @param  boolean $readonly
     * @return string
     */
    private static function getNumericInput($header, $values = array(), $readonly = false)
    {
        $value        = self::getValue($header, $values);
        $readonly     = self::getReadOnly($readonly);
        $required     = self::getRequired($header);

        $numericInput = file_get_contents(__DIR__ . '/templates/numeric_input.html');
        $numericInput = str_replace('{column_name}', $header->column_name, $numericInput);
        $numericInput = str_replace('{title}', $header->title, $numericInput);
        $numericInput = str_replace('{value}', $value, $numericInput);
        $numericInput = str_replace('{required}', $required, $numericInput);
        $numericInput = str_replace('{numeric_precision}', $header->numeric_precision, $numericInput);
        $numericInput = str_replace('{readonly}', $readonly, $numericInput);

        return $numericInput;
    }

    /**
     * Build Test field
     * 
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    private static function getVarcharInput($header, $values = array(), $readonly)
    {
        $value        = self::getValue($header, $values);
        $readonly     = self::getReadOnly($readonly);
        $required     = self::getRequired($header);
        
        $varcharInput = file_get_contents(__DIR__ . '/templates/varchar_input.html');
        $varcharInput = str_replace('{column_name}', $header->column_name, $varcharInput);
        $varcharInput = str_replace('{title}', $header->title, $varcharInput);
        $varcharInput = str_replace('{value}', $value, $varcharInput);
        $varcharInput = str_replace('{required}', $required, $varcharInput);
        $varcharInput = str_replace('{character_maximum_length}', $header->character_maximum_length, $varcharInput);
        $varcharInput = str_replace('{readonly}', $readonly, $varcharInput);

        return $varcharInput;
    }

    /**
     * Build Test field
     * 
     * @param  StdObj   $header
     * @param  array    $values
     * @param  boolean  $readonly
     * @return string
     */
    private static function getTextInput($header, $values = array(), $readonly)
    {
        $value     = self::getValue($header, $values);
        $readonly  = self::getReadOnly($readonly);
        $required  = self::getRequired($header);
        
        $textInput = file_get_contents(__DIR__ . '/templates/text_input.html');
        $textInput = str_replace('{column_name}', $header->column_name, $textInput);
        $textInput = str_replace('{title}', $header->title, $textInput);
        $textInput = str_replace('{value}', $value, $textInput);
        $textInput = str_replace('{required}', $required, $textInput);
        $textInput = str_replace('{readonly}', $readonly, $textInput);

        return $textInput;
    }

    /**
     * Build Date field
     * 
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    private static function getDateInput($header, $values = array(), $readonly)
    {
        $value     = self::getValue($header, $values);
        $readonly  = self::getReadOnly($readonly);
        $required  = self::getRequired($header);
        
        $dateInput = file_get_contents(__DIR__ . '/templates/date_input.html');
        $dateInput = str_replace('{column_name}', $header->column_name, $dateInput);
        $dateInput = str_replace('{title}', $header->title, $dateInput);
        $dateInput = str_replace('{value}', $value, $dateInput);
        $dateInput = str_replace('{required}', $required, $dateInput);
        $dateInput = str_replace('{readonly}', $readonly, $dateInput);

        return $dateInput;
    }

    /**
     * Build Timestamp field
     * 
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    private static function getTimestampInput($header, $values = array(), $readonly)
    {
        $value          = self::getValue($header, $values);
        $readonly       = self::getReadOnly($readonly);
        $required       = self::getRequired($header);
        
        $timestampInput = file_get_contents(__DIR__ . '/templates/timestamp_input.html');
        $timestampInput = str_replace('{column_name}', $header->column_name, $timestampInput);
        $timestampInput = str_replace('{title}', $header->title, $timestampInput);
        $timestampInput = str_replace('{value}', $value, $timestampInput);
        $timestampInput = str_replace('{required}', $required, $timestampInput);
        $timestampInput = str_replace('{readonly}', $readonly, $timestampInput);

        return $timestampInput;
    }

    /**
     * Build Enum Dropdown
     * 
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    private static function getEnumInput($header, $values = array(), $readonly)
    {
        $options     = self::getEnumOptions($header);

        $value       = self::getValue($header, $values);
        $disabled    = self::getDisabled($readonly);
        $required    = self::getRequired($header);
        $title       = self::getPrettyTitle($header->column_name);
        $optionsHtml = self::getOptions($options, $value, $title);

        $enumInput   = file_get_contents(__DIR__ . '/templates/enum_input.html');
        $enumInput   = str_replace('{column_name}', $header->column_name, $enumInput);
        $enumInput   = str_replace('{title}', $header->title, $enumInput);
        $enumInput   = str_replace('{value}', $value, $enumInput);
        $enumInput   = str_replace('{required}', $required, $enumInput);
        $enumInput   = str_replace('{disabled}', $disabled, $enumInput);
        $enumInput   = str_replace('{optionsHtml}', $optionsHtml, $enumInput);
        
        return $enumInput;
    }

    /**
     * Get Options for Enum Dropdow
     * 
     * @param  StdObj $header
     * @return string
     */
    private static function getEnumOptions($header)
    {

        preg_match("/^enum\(\'(.*)\'\)$/", $header->column_type, $matches);
        $enum = explode("','", $matches[1]);

        $options = array();

        foreach ($enum as $option) {
            $obj       = new \stdClass;
            $obj->id   = $option;
            $obj->name = $option;
            $options[] = $obj;
        }

        return $options;
    }

    /**
     * Build Dropdown
     * 
     * @param  StdObj   $header
     * @param  array    $values
     * @param  boolean  $readonly
     * @param  array    $options
     * @return string
     */
    private static function getDropDown($header, $values = array(), $readonly, $options = array())
    {
        $value         = self::getValue($header, $values);
        $readonly      = self::getReadOnly($readonly);
        $required      = self::getRequired($header);
        $title         = self::getPrettyTitle($header->column_name);
        $optionsHtml   = self::getOptions($options, $value, $title);
        $route         = strtolower($header->title);
        $newButton     = "";
        $newClass      = "";
            
        if (!$readonly) {
            $newClass      = 'input-group';
            $newButton     = file_get_contents(__DIR__ . '/templates/new_button.html');
            $newButton     = str_replace('{route}', "/$route/create", $newButton);
        }
            
        $dropdownInput = file_get_contents(__DIR__ . '/templates/dropdown_input.html');
        $dropdownInput = str_replace('{column_name}', $header->column_name, $dropdownInput);
        $dropdownInput = str_replace('{title}', $title, $dropdownInput);
        $dropdownInput = str_replace('{newClass}', $newClass, $dropdownInput);
        $dropdownInput = str_replace('{required}', $required, $dropdownInput);
        $dropdownInput = str_replace('{readonly}', $readonly, $dropdownInput);
        $dropdownInput = str_replace('{optionsHtml}', $optionsHtml, $dropdownInput);
        $dropdownInput = str_replace('{newButton}', $newButton, $dropdownInput);

        return $dropdownInput;
    }

    /**
     * Get Options for Dropdown
     * 
     * @param  array  $options
     * @param  string $value
     * @param  string $title
     * @return string
     */
    private static function getOptions($options = array(), $value = '', $title = 'option')
    {
        $html = '<option value="0">Select a '.$title.'</option>';

        foreach ($options as $option) {
            $selected = '';
            
            if ($option->id == $value) {
                $selected = 'selected';
            }

            $html .= "<option value='$option->id' $selected >$option->name</option>";
        }

        return $html;
    }

    /**
     * Format title
     * 
     * @param  string $column
     * @return string
     */
    private static function getPrettyTitle($column)
    {
        return ucfirst(str_replace('_id', '', $column));
    }

    /**
     * Extract foreign data for relationships
     * 
     * @return array
     */
    public static function getForeignDataDropDown(Model $model)
    {
        $tableRelationShips = self::getRelationShips($this);

        $headers          = $model->getHeaders();
        $inputs           = array();

        foreach ($headers as $header) {
            if (!empty($tableRelationShips) && $foreignKey = self::isForeignKey($tableRelationShips, $header->column_name)) {
                $inputs[$header->column_name] = self::getForeignData($foreignKey);
            }
        }
        
        return $inputs;
    }

    /**
     * Dynamically call methods
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([self, $method], $parameters);
    }
}
