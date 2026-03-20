<?php

/**
 * insert.php
 * Endpoint to insert a new row into a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function insert() {
    $table   = request_data['parameters']['table']   ?? null;
    $values  = request_data['parameters']['values']  ?? null;

    if (!isset($table))      http_response(400, ["error" => "Missing table name"]);
    if (!is_array($values))  http_response(400, ["error" => "Values and columns must be an array"]);
    if (count($values) == 0) http_response(400, ["error" => "Values and columns must not be empty"]);

    $keys   = array_keys($values);
    $values = array_values($values);

    foreach ($keys as $value) {
        if (!is_string($value)) http_response(400, ["error" => "Values and columns must be an associative array"]);
    }

    $new_id = sqlInsert($table, $keys, $values);
    
    define("new_id", $new_id);
    define("keys",   $keys);
    define("values", $values);
    define("table",  $table);

    $new_row = null;

    if (new_id > 0) {
        $primary_key = getPrimaryKey($table);
        $new_row = sqlSelectRow($table, "*", "`$primary_key` = " . new_id);
    }

    $last_update = getTableLastUpdateTime($table);
    
    include_once getcwd() . '/' . request_method . '/events.php';
    after_insert($new_row);
    
    http_response(200, ["values" => ["new_id" => new_id], "table_last_update" => $last_update, "new_row" => $new_row]);
}
insert();
