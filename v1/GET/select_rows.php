<?php

/**
 * select_rows.php
 * Endpoint to select multiple rows from a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function select_rows() {
    $table = request_data['parameters']['table'] ?? null;
    $fields = request_data['parameters']['fields'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $orderby = request_data['parameters']['orderby'] ?? null;
    $limit = request_data['parameters']['limit'] ?? null;
    $groupby = request_data['parameters']['groupby'] ?? '';

    $rows = sqlSelectRows($table, $fields, $where, $orderby, $limit, $groupby);
    
    if (isset(request_data['payload']['format'])) {
        $format = request_data['payload']['format'];
        switch ($format) {
            case 'datatable':

                break;
            default:
                break;
        }
    }

    http_response(200, ["values" => ["rows" => $rows], "table_last_update" => getTableLastUpdateTime($table)]);
}

select_rows();
