<?php

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
eval($helpers->init_global_config());

// https://api-extractor-dot-annalect-api-jobs.appspot.com/run-bigquery-std?projectId=annalect-api-jobs&datasetId=programmatic&tableId=inventory&object=programmatic/input/dbm/inventory.csv&disposition=truncate


// BigQuery
$extractions['projectId'] = $_GET['projectId'];
$extractions['datasetId'] = $_GET['datasetId'];
$extractions['tableId'] = $_GET['tableId'];
$extractions['object'] = $_GET['object'];
$extractions['disposition'] = $_GET['disposition'];
$helpers->upload_big_query($extractions);