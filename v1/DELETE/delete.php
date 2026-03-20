<?php
/**
 * delete.php
 * Endpoint to delete record(s) in a table
 * DO NOT MODIFY THIS FILE.
 * @author ruvenss <ruvenss@gmail.com>
 */
function delete() {
    $table = request_data['parameters']['table'] ?? null;
    $where = request_data['parameters']['where'] ?? null;

    if (!isset($table)) http_response(400, ["error" => "Missing table"]);
    if (!isset($where)) http_response(400, ["error" => "Missing where"]);
    
    if (sqlDelete($table, $where)) {
        $affectedRows = dbconn->affected_rows;
        $last_update = getTableLastUpdateTime($table);
        include_once getcwd() . '/' . request_method . '/events.php';
        http_response(200, ["values" => [], "table_last_update" => $last_update, "affected_rows" => $affectedRows]);
    } else {
        http_response(400, ["error" => "Delete failed"]);
    }
}
delete();