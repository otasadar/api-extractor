<?php
use google\appengine\api\log\LogService;
include_once __DIR__ . '/api/helpers.php';
include_once __DIR__ . '/config-global.php';
$helpers = new helpers();

//Refresh access token
$access_token = $helpers->get_access_token('1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com', 'PAx5fz386w0groUL8JFgdVuQ', '1/T32mKg5ITADEUXA5viQuDBNyg8yhDuIyCO3z6Mqy2cM');

syslog(LOG_DEBUG, 'ACCESS TOKEN-> ' . $access_token);

//Call headers
$headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

//End point
$api_version = $extractions['global']['google_bigquery']['api_version'];
$endpoint = "https://content.googleapis.com/bigquery/$api_version/projects/annalect-api-jobs/jobs?alt=json";

//Payload data
$payload = '{"configuration":{"load":{"destinationTable":{"tableId":"' . $_GET['tableId'] . '","projectId":"annalect-api-jobs","datasetId":"' . $_GET['datasetId'] . '"},"autodetect":true,"sourceUris":["gs://annalect-dashboarding/' . $_GET['filePath'] . '"]}}}';

syslog(LOG_DEBUG, 'PAYLOAD-> ' . $payload);

syslog(LOG_DEBUG, 'PAYLOAD-> ' . json_decode(json_encode($payload)));

//CURL request
$curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

syslog(LOG_DEBUG, 'curl response-> ' . $curl_response);

