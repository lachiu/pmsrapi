<?php
/**
 * MicroService Restful API
 * 
 * This is the main entry point for the MicroService Restful API
 * Do not add your code here, create your code and files in the GET, POST, PUT, DELETE folder
 * IF YOU ADD YOUR CODE HERE IT WILL BE OVERWRITTEN ON THE NEXT UPDATE
 * @category MicroService
 * @package  MicroService_Restful_API
 * @version  1.0.0
 * @since    1.0.0
 * @link    https://github.com/ruvenss/pmsrapi
 * */

if (!stream_resolve_include_path(config_path)) {
    http_response(500, ["error" => "Configuration file not found at " . config_path]);
}

if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    http_response(400, ["error" => "Bad Request only application/json is allowed"]);
}

if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    http_response(401, ["error" => "Unauthorized"]);
}

$token = explode(" ", $_SERVER['HTTP_AUTHORIZATION']);

if (count($token) != 2) {
    http_response(401, ["error" => "Unauthorized"]);
}

$token = $token[1];

if (!in_array($token, ms_secrets)) {
    http_response(401, ["error" => "Unauthorized"]);
}

include_once getcwd() . '/config.php';
define("ms_secrets", json_decode(file_get_contents(config_path), true));
define("ms_logserver_token", ms_secrets['ms_logserver_token']);
define("ms_environment", ms_secrets['env']);
define("request_method", $_SERVER['REQUEST_METHOD']);
define("request_body", file_get_contents('php://input'));

if (!isset(ms_secrets['allowed_functions'][request_method])) {
    http_response(405, ["error" => "Method " . request_method . " Not Allowed"]);
}

if (!json_validate(request_body)) {
    http_response(400, ["error" => "Bad Request invalid JSON"]);
}
            
define("request_data", json_decode(request_body, true));

if (!isset(request_data['function'])) {
    http_response(405, ["error" => "Function not declared"]);
}

if (!in_array(request_data['function'], ms_secrets['allowed_functions'][request_method])) {
    http_response(405, ["error" => "Function not declared"]);
}

if (stream_resolve_include_path(getcwd() . '/vendor/autoload.php')) {
    include_once getcwd() . '/vendor/autoload.php';
}

if (stream_resolve_include_path(getcwd() . '/general/db.php')) {
    include_once getcwd() . '/general/db.php';
}

if (stream_resolve_include_path(getcwd() . '/general/custom_functions.php')) {
    include_once getcwd() . '/general/custom_functions.php';
}
 
if (is_readable(getcwd() . '/' . request_method . '/' . request_data['function'] . '.php')) {
    include_once getcwd() . '/' . request_method . '/' . request_data['function'] . '.php';
} else {
    http_response(405, ["error" => "Function not found"]);
}

function http_response($http_code = 200, $data = null)
{
    $response = ms_restful_responses;
    $http_headers = ms_http_headers;
    $success = false;
    if ($http_code == 200) {
        $success = true;
    }
    header("HTTP/1.1 $http_code $response[$http_code]");
    header("X-Powered-By: PMSRAPI");
    foreach ($http_headers as $key => $value) {
        header("$key: $value");
    }
    header("MicroService: " . ms_name);
    header("MicroService-Version: " . ms_version);
    if ($data) {
        $response = ["success" => $success, "data" => $data];
        echo json_encode($response);
    }
    if (defined("dbconn")) {
        dbconn->close();
    }
    die();
}
function http_rest($node, $function, $payload, $parameters, $method = "GET")
{
    if (isset(ms_secrets['universe'])) {
        $data_payload = [];
        for ($i = 0; $i < count(ms_secrets['universe']); $i++) {
            if (ms_secrets['universe'][$i]['name'] == $node) {
                if (ms_secrets['universe'][$i]['ssl'] == true) {
                    $url = "https://" . ms_secrets['universe'][$i]['ip'] . ":" . ms_secrets['universe'][$i]['port'] . "/api/v1/";
                } else {
                    $url = "http://" . ms_secrets['universe'][$i]['ip'] . ":" . ms_secrets['universe'][$i]['port'] . "/api/v1/";
                }
                $token = ms_secrets['universe'][$i]['token'];
                break;
            }
        }
        $data_payload['function'] = $function;
        $data_payload['parameters'] = $parameters;
        $data_payload['payload'] = $payload;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data_payload),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));
        curl_exec($curl);
        if (curl_errno($curl)) {
            curl_close($curl);
            return false;
        } else {
            $response = curl_exec($curl);
            curl_close($curl);
            if (json_validate($response)) {
                return json_decode($response, true);
            } else {
                return $response;
            }
        }
    } else {
        return false;
    }
}
/**
 * Log an event to the log server
 * @param string $changes = {"field":"value","field2":"value2"} or null
 * @param string $action = 'created','updated','deleted','view','bitbucket_notification_received','github_notification_received'/.. etc up to you
 * @param string $log_type = 'task','project','user','team','notification','bitbucket','github','log' etc up to you
 * @param int $created_by User ID
 * @param string $log_type_title = if is not defined the MicroService Name will be used
 * @param int $log_type_id = if is not defined 1 will be used
 * @param string $log_for = if is not defined the MicroService Name will be used (max 30 characters)
 * @param int $log_for_id = if is not defined 0 will be used
 * @param string $log_for2 = if is not defined the MicroService Name will be used
 * @param int $log_for_id2 = if is not defined 0 will be used  (max 30 characters)
 * @param int $deleted = 0 or 1 if the record was deleted
 * @return boolean
 */
function log_event($changes, $env = "dev", $action = "updated", $log_type = "task", $created_by = 1, $log_type_title = ms_name, $log_type_id = 1, $log_for = ms_name, $log_for_id = 0, $log_for2 = ms_name, $log_for_id2 = null, $deleted = 0)
{
    if ($env == ms_environment) {
        $logserver = ms_logserver;
        $token = ms_logserver_token;
        $data = ["microservice" => ms_name, "version" => ms_version, "created_at" => date("Y-m-d H:i:s"), "changes" => $changes, "action" => $action, "log_type" => $log_type, "created_by" => $created_by, "log_type_title" => $log_type_title, "log_type_id" => $log_type_id, "log_for" => $log_for, "log_for_id" => $log_for_id, "log_for2" => $log_for2, "log_for_id2" => $log_for_id2, "deleted" => $deleted];
        // There is 2 type of log we can send to the log server or we can save it in the local file
        if ($logserver == null || $logserver == "") {
            // Save the log in the local file
            file_put_contents(config_path['local_log']['path'], json_encode($data) . PHP_EOL, FILE_APPEND);
            return true;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => ms_logserver,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . ms_logserver_token
            ),
        ));
        curl_exec($curl);
        if (curl_errno($curl)) {
            curl_close($curl);
            return false;
        } else {
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
    } else {
        return false;
    }
}