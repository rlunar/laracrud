<?php

namespace LaraCrud;

abstract class LaraCrudGrammar
{
    public static function getColumns()
    {
        return array('column_name', 'data_type', 'character_maximum_length', 'numeric_precision', 'is_nullable', 'column_type');
    }
}
