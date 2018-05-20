<?php

include_once __DIR__ . '/api/helpers.php';
include_once __DIR__ . '/config-global.php';
$helpers = new helpers();

// https://api-extractor-dot-annalect-api-jobs.appspot.com/run-bigquery?tableId=phd_aio_final&datasetId=aio_phd&filePath=aio_phd/output/phd_aio_data.csv&schema=phd-aio-schema.json
///run-bigquery?projectId=aio_phd&tableId=aio_phd&datasetId=annalect_dashboarding

//Refresh access token
$access_token = $helpers->get_access_token($extractions['global']['google']['client_id'],
    $extractions['global']['google']['client_secret'],
    $extractions['global']['google_bigquery']['refresh_token']);

syslog(LOG_DEBUG, 'ACCESS TOKEN-> ' . $access_token);

//Call headers
$headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

//End point
$api_version = $extractions['global']['google_bigquery']['api_version'];
$endpoint = "https://www.googleapis.com/bigquery/$api_version/projects/annalect-api-jobs/jobs?alt=json";
$list_query_projects = file_get_contents('https://storage.googleapis.com/annalect-dashboarding/config/queries.json'); //???
echo json_decode($list_query_projects,true)[$_GET['projectId']]['query']; // ???

//Payload data
$payload = '{
    "configuration": {
        "query": {
            "query": "' . json_decode($list_query_projects,true)[$_GET['projectId']]['query'] . '",
            "allowLargeResults": true,
            "destinationTable": {
				"tableId": "' . $_GET['tableId'] . '",
				"projectId": "annalect-api-jobs",
				"datasetId": "' . $_GET['datasetId'] . '"
            },
        "defaultDataset": {
            "datasetId": "annalect_dashboarding",
            "projectId": "annalect-api-jobs"
        },
        "writeDisposition": "WRITE_TRUNCATE"
    }
  }
}';


echo "<pre>";
echo $payload;

syslog(LOG_DEBUG, 'PAYLOAD-> ' . $payload);

syslog(LOG_DEBUG, 'PAYLOAD-> ' . json_decode(json_encode($payload)));

//CURL request
$curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

syslog(LOG_DEBUG, 'curl response-> ' . json_encode($curl_response, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));