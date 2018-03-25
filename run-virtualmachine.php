<?php

// Active example
// https://api-extractor-dot-annalect-api-jobs.appspot.com/run-virtualmachine?action=start
// https://api-extractor-dot-annalect-api-jobs.appspot.com/run-virtualmachine?action=stop

use google\appengine\api\log\LogService;
include_once __DIR__ . '/api/helpers.php';
include_once __DIR__ . '/config-global.php';
$helpers = new helpers();
$action = $_GET['action'];

//Refresh access token
$client_id = $extractions['global']['google']['client_id'];
$client_secret = $extractions['global']['google']['client_secret'];
$refresh_token = $extractions['global']['google_compute']['refresh_token'];
$api_version = $extractions['global']['google_compute']['api_version'];

$access_token = $helpers->get_access_token($client_id, $client_secret, $refresh_token);

//Call headers
$headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

//End point
$endpoint = "https://www.googleapis.com/compute/$api_version/projects/annalect-api-jobs/zones/asia-south1-a/instances/jobs-executor-vm/$action";

//Payload data
$payload = '';

//CURL request
$curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);
