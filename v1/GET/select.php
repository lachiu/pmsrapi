<?php
/**
 * select.php
 * Endpoint to select a single field from a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function select() {
    $table = request_data['parameters']['table'] ?? null;
    $field = request_data['parameters']['field'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $last_update = getTableLastUpdateTime($table);
    http_response(200, ["values" => [$field => sqlSelect($table, $field, $where)], "table_last_update" => $last_update]);
}

select();
