<?php
use google\appengine\api\log\LogService;
include_once __DIR__ . '/api/helpers.php';
include_once __DIR__ . '/config-global.php';
$helpers = new helpers();

// https://api-extractor-dot-annalect-api-jobs.appspot.com/run-bigquery?tableId=phd_aio_final&datasetId=aio_phd&filePath=aio_phd/output/phd_aio_data.csv&schema=phd-aio-schema.json

//Refresh access token
$access_token = $helpers->get_access_token('1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com', 'PAx5fz386w0groUL8JFgdVuQ', '1/T32mKg5ITADEUXA5viQuDBNyg8yhDuIyCO3z6Mqy2cM');

syslog(LOG_DEBUG, 'ACCESS TOKEN-> ' . $access_token);

//Call headers
$headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

//End point
$api_version = $extractions['global']['google_bigquery']['api_version'];
$endpoint = "https://content.googleapis.com/bigquery/$api_version/projects/annalect-api-jobs/jobs?alt=json";
$schema = file_get_contents('gs://annalect-dashboarding/config/'. $_GET['schema'] );

//Payload data
$payload = '{
	"configuration": {
		"load": {
			"destinationTable": {
				"tableId": "' . $_GET['tableId'] . '",
				"projectId": "annalect-api-jobs",
				"datasetId": "' . $_GET['datasetId'] . '"
			},
			"skipLeadingRows": 1,
			"writeDisposition" : "WRITE_TRUNCATE",
	        "schema": { '.$schema.'},
			"sourceUris": ["gs://annalect-dashboarding/' . $_GET['filePath'] . '"]
		}
	}
}';

echo "<pre>";
echo $payload;

syslog(LOG_DEBUG, 'PAYLOAD-> ' . $payload);

syslog(LOG_DEBUG, 'PAYLOAD-> ' . json_decode(json_encode($payload)));

//CURL request
$curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

syslog(LOG_DEBUG, 'curl response-> '.json_encode($curl_response, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));

