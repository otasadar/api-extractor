<?php

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
eval($helpers->init_global_config()); //init extractions object

// /run-combine?folder=omd-aio/input/facebook&destination=omd-aio-fb-all.csv&files=omd-aio-fb-1-of-4.csv,omd-aio-fb-2-of-4.csv,omd-aio-fb-3-of-4.csv,omd-aio-fb-4-of-4.csv

$folder = $_GET['folder'];
$files = explode(',', $_GET['files']);
$destination= $_GET['destination'];

// Empty main object
$bucket = $extractions['global']['google_storage']['bucket'];
file_put_contents("gs://$bucket/$folder/$destination", '');


//Refresh access token
$access_token = $helpers->get_access_token($extractions['global']['google']['client_id'],
    $extractions['global']['google']['client_secret'],
    $extractions['global']['google_bigquery']['refresh_token']);


foreach ($files as $sourceObject){
    $helpers->compose_two_files_storage($extractions, "$folder/$destination", "$folder/$sourceObject");
}

