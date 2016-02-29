<?php

namespace LaraCrud;

use LaraCrud\LaraCrudGrammar;

trait LaraCrudModel
{
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

    /**
     * Get html inputs from DB Schema
     * @param  array   $values
     * @param  boolean $readonly
     * @return array
     */
    public function getInputs($values = array(), $readonly = false)
    {
        $tableRelationShips = LaraCrud::getRelationShips($this);

        $headers          = $this->getHeaders();
        $inputs           = array();

        foreach ($headers as $header) {
            if ($header->column_name === 'id') {
                continue;
            }

            if (!empty($tableRelationShips) && $foreignKey = LaraCrud::isForeignKey($tableRelationShips, $header->column_name)) {
                $inputs[] = $this->getDropDown($header, $values, $readonly, LaraCrud::getForeignData($foreignKey));
                continue;
            }

            $inputs[] = $this->getInput($header, $values, $readonly);
        }
        
        return $inputs;
    }

    /**
     * Extract foreign data for relationships
     * @return array
     */
    public function getForeignData()
    {
        $tableRelationShips = LaraCrud::getRelationShips($this);

        $headers          = $this->getHeaders();
        $inputs           = array();

        foreach ($headers as $header) {
            if (!empty($tableRelationShips) && $foreignKey = LaraCrud::isForeignKey($tableRelationShips, $header->column_name)) {
                $inputs[$header->column_name] = LaraCrud::getForeignData($foreignKey);
            }
        }
        
        return $inputs;
    }

    /**
     * Get input according to the type of column
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    protected function getInput($header, $values = array(), $readonly = false)
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
                return $this->getNumericInput($header, $values, $readonly);
                break;
            case 'varchar':
                return $this->getVarcharInput($header, $values, $readonly);
                break;
            case 'text':
            case 'mediumtext':
            case 'longtext':
                return $this->getTextInput($header, $values, $readonly);
                break;
            case 'date':
                return $this->getDateInput($header, $values, $readonly);
                break;
            case 'datetime':
            case 'timestamp':
                return $this->getTimestampInput($header, $values, $readonly);
                break;
        }
    }

    protected function getValue($header, $values = array())
    {
        return !empty($values) && isset($values->{$header->column_name}) ? $values->{$header->column_name} : '';
    }

    protected function getReadOnly($readonly = false)
    {
        return $readonly ? 'readonly="true"' : '';
    }

    protected function getRequired($header)
    {
        return $header->is_nullable == 'NO' ? 'required="required"' : '';
    }

    /**
     * Build Numeric field
     * @param  StdObj  $header
     * @param  array   $values]
     * @param  boolean $readonly
     * @return string
     */
    protected function getNumericInput($header, $values = array(), $readonly = false)
    {
        $value        = $this->getValue($header, $values);
        $readonly     = $this->getReadOnly($readonly);
        $required     = $this->getRequired($header);

        $numericInput = file_get_contents( __DIR__ . '/templates/numeric_input.html');
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
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    protected function getVarcharInput($header, $values = array(), $readonly)
    {
        $value        = $this->getValue($header, $values);
        $readonly     = $this->getReadOnly($readonly);
        $required     = $this->getRequired($header);
        
        $varcharInput = file_get_contents( __DIR__ . '/templates/varchar_input.html');
        $varcharInput = str_replace('{column_name}', $header->column_name, $varcharInput);
        $varcharInput = str_replace('{title}', $header->title, $varcharInput);
        $varcharInput = str_replace('{value}', $value, $varcharInput);
        $varcharInput = str_replace('{required}', $required, $varcharInput);
        $varcharInput = str_replace('{character_maximum_length}', $header->character_maximum_length, $varcharInput);
        $varcharInput = str_replace('{readonly}', $readonly, $varcharInput);

        return $varcharInput;
    }

    protected function getTextInput($header, $values = array(), $readonly)
    {
		$value     = $this->getValue($header, $values);
		$readonly  = $this->getReadOnly($readonly);
		$required  = $this->getRequired($header);
		
		$textInput = file_get_contents( __DIR__ . '/templates/text_input.html');
		$textInput = str_replace('{column_name}', $header->column_name, $textInput);
		$textInput = str_replace('{title}', $header->title, $textInput);
		$textInput = str_replace('{value}', $value, $textInput);
		$textInput = str_replace('{required}', $required, $textInput);
		$textInput = str_replace('{readonly}', $readonly, $textInput);

        return $textInput;
    }

    /**
     * Build Date field
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    protected function getDateInput($header, $values = array(), $readonly)
    {
		$value     = $this->getValue($header, $values);
		$readonly  = $this->getReadOnly($readonly);
		$required  = $this->getRequired($header);
		
		$dateInput = file_get_contents( __DIR__ . '/templates/date_input.html');
		$dateInput = str_replace('{column_name}', $header->column_name, $dateInput);
		$dateInput = str_replace('{title}', $header->title, $dateInput);
		$dateInput = str_replace('{value}', $value, $dateInput);
		$dateInput = str_replace('{required}', $required, $dateInput);
		$dateInput = str_replace('{readonly}', $readonly, $dateInput);

        return $dateInput;
    }

    /**
     * Build Timestamp field
     * @param  StdObj  $header
     * @param  array   $values
     * @param  boolean $readonly
     * @return string
     */
    protected function getTimestampInput($header, $values = array(), $readonly)
    {
		$value          = $this->getValue($header, $values);
		$readonly       = $this->getReadOnly($readonly);
		$required       = $this->getRequired($header);
		
		$timestampInput = file_get_contents( __DIR__ . '/templates/timestamp_input.html');
		$timestampInput = str_replace('{column_name}', $header->column_name, $timestampInput);
		$timestampInput = str_replace('{title}', $header->title, $timestampInput);
		$timestampInput = str_replace('{value}', $value, $timestampInput);
		$timestampInput = str_replace('{required}', $required, $timestampInput);
		$timestampInput = str_replace('{readonly}', $readonly, $timestampInput);

        return $timestampInput;
    }

    protected function getDropDown($header, $values = array(), $readonly, $options = array())
    {
		$value         = $this->getValue($header, $values);
		$readonly      = $this->getReadOnly($readonly);
		$required      = $this->getRequired($header);
		$title         = $this->getPrettyTitle($header->column_name);
		$optionsHtml   = $this->getOptions($options, $value, $title);
		$route         = strtolower($header->title);
		$newButton     = "";
		$newClass      = "";
			
		if (!$readonly) {
			$newClass      = 'input-group';
			$newButton     = file_get_contents( __DIR__ . '/templates/new_button.html');
			$newButton     = str_replace('{route}', "/$route/create", $newButton);
		}
			
		$dropdownInput = file_get_contents( __DIR__ . '/templates/dropdown_input.html');
		$dropdownInput = str_replace('{column_name}', $header->column_name, $dropdownInput);
		$dropdownInput = str_replace('{title}', $title, $dropdownInput);
		$dropdownInput = str_replace('{newClass}', $newClass, $dropdownInput);
		$dropdownInput = str_replace('{required}', $required, $dropdownInput);
		$dropdownInput = str_replace('{readonly}', $readonly, $dropdownInput);
		$dropdownInput = str_replace('{optionsHtml}', $optionsHtml, $dropdownInput);
		$dropdownInput = str_replace('{newButton}', $newButton, $dropdownInput);

        return $dropdownInput;
    }

    protected function getOptions($options = array(), $value = '', $title = 'option')
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

    protected function getPrettyTitle($column)
    {
        return ucfirst(str_replace('_id', '', $column));
    }
}
