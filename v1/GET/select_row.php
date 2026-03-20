<?php
/**
 * select_row.php
 * Endpoint to select a single row from a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function select_row() {
    $table = request_data['parameters']['table'] ?? null;
    $fields = request_data['parameters']['fields'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $orderby = request_data['parameters']['orderby'] ?? null;
    $row = sqlSelectRow($table, $fields, $where, $orderby);
    http_response(200, ["values" => ["row" => $row], "table_last_update" => getTableLastUpdateTime($table)]);
}

select_row();