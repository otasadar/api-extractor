<?php

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
eval($helpers->init_global_config());

//  url: /run-bigquery?queryId=hs_aio&projectId=annalect-api-jobs&datasetId=annalect_dashboarding&tableId=hs_aio
//  url: /run-bigquery?queryId=aio_phd&projectId=annalect-api-jobs&datasetId=annalect_dashboarding&tableId=aio_phd


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
$list_query_projects = file_get_contents('https://storage.googleapis.com/annalect-dashboarding/config/bigqueries.json'); //???
echo json_decode($list_query_projects,true)['queries'][$_GET['queryId']];

//Payload data
$payload = '{
    "configuration": {
        "query": {
            "query": "' . json_decode($list_query_projects,true)['queries'][$_GET['queryId']] . '",
            "allowLargeResults": true,
            "destinationTable": {
				"tableId": "' . $_GET['tableId'] . '",
				"projectId": "' . $_GET['projectId'] . '",
				"datasetId": "' . $_GET['datasetId'] . '"
            },
        "defaultDataset": {
            "datasetId": "' . $_GET['datasetId'] . '",
            "projectId": "' . $_GET['projectId'] . '"
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