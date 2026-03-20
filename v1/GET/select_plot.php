<?php
/**
 * select_plot.php
 * Endpoint to select a plot from a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function select_plot() {
    $table = request_data['parameters']['table'] ?? null;
    $where = request_data['parameters']['where'] ?? null;
    $orderby = request_data['parameters']['orderby'] ?? null;
    $limit = request_data['parameters']['limit'] ?? null;
    $groupby = request_data['parameters']['groupby'] ?? null;
    $fields = explode(",", request_data['parameters']['fields'] ?? '');
    $field_x = trim($fields[0]);
    $field_y = trim($fields[1]);
    $plot = sqlSelectPlot($table, $field_x, $field_y, $where, $orderby, $limit, $groupby);
    http_response(200, ["values" => ["rows" => $plot], "table_last_update" => getTableLastUpdateTime($table)]);
}

select_plot();