<?php


use google\appengine\api\log\LogService;
include_once __DIR__ . '/api/helpers.php';
include_once __DIR__ . '/config-global.php';
$helpers = new helpers();


$bucket = $extractions['global']['google_storage']['bucket'];

//Create temp file to append
$content = '"DBM","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","","",""'."\n";
file_put_contents("gs://$bucket/aio_phd/output/dbm-footer.csv", $content);

// Append file
$file_path = "aio_phd/output/phd_aio_data.csv";
$file_path_encode = urlencode($file_path);
$tmp_file_path = "aio_phd/output/dbm-footer.csv";


$access_token = $helpers->get_storage_access_token($extractions);
$headers = array('Authorization: Bearer ' . $access_token,
    'Accept: application/json',
    'Content-Type: application/json');
$payload = '{"sourceObjects":[{"name":"' . $file_path . '"},{"name":"' . $tmp_file_path . '"}]}';
$version = $extractions['global']['google_storage']['api_version'];
$endpoint = "https://www.googleapis.com/storage/$version/b/$bucket/o/$file_path_encode/compose";
$response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);
