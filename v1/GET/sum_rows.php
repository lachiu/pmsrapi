<?php
/**
 * This file is used to sum the values of a field in a table.
 * The table name, field name and where clause are passed as parameters.
 * The response is the total sum of the field in the table.
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function sum_rows() {
    $table = request_data['parameters']['table'] ?? null;
    $field = request_data['parameters']['field'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $sum = sqlSum($table, $field, $where);
    http_response(200, ["values" => ["total" => $sum], "table_last_update" => getTableLastUpdateTime($table)]);
}
sum_rows();