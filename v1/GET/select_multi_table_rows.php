<?php
function select_multi_table_rows() {
    $tables = request_data['parameters']['tables'] ?? null;
    $fields = request_data['parameters']['fields'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $orderby = request_data['parameters']['orderby'] ?? null;
    $limit = request_data['parameters']['limit'] ?? null;
    $tablerows = [];
    $rows = [];
    foreach ($tables as $table) {
        $tablerows[$table] = sqlSelectRows($table, $fields, $where, $orderby, $limit);
        $table_result = $tablerows[$table];
        if (sizeof($table_result)) {
            for ($i = 0; $i < sizeof($table_result); $i++) {
                array_push($rows, $table_result[$i]);
            }
        }
    }
    http_response(200, ["values" => ["rows" => $rows]]);
}

select_multi_table_rows();
