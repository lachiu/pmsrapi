<?php
/**
 * This file is used to count the number of rows in a table.
 * The table name and where clause are passed as parameters.
 * The response is the total number of rows in the table.
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function count_rows() {
    $table = request_data['parameters']['table'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    
    $count = sqlCount($table, $where);
    http_response(200, ["values" => ["total" => $count], "table_last_update" => getTableLastUpdateTime($table)]);
}

count_rows();