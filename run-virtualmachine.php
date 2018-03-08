<?php
use google\appengine\api\log\LogService;
include_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
$action = $_GET['action'];

//Refresh access token
$access_token = $helpers->get_access_token('1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com', 'PAx5fz386w0groUL8JFgdVuQ', '1/sVw1xELv02Lqo-7zuu45flooZL8u8OOn07pxP6NU7Q8');

//Call headers
$headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

//End point
$endpoint = 'https://www.googleapis.com/compute/v1/projects/annalect-api-jobs/zones/asia-south1-a/instances/jobs-executor-vm/' . $action;

//Payload data
$payload = '';

//CURL request
$curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);
