<?php

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
eval($helpers->init_global_config());


//Refresh access token
$access_token = $helpers->get_access_token($extractions['global']['google']['client_id'],
    $extractions['global']['google']['client_secret'],
    $extractions['global']['google_bigquery']['refresh_token']);

$objects = ['aio_phd/input/facebook/fb_part_1_of_4.csv',
            'aio_phd/input/facebook/fb_part_2_of_4.csv',
            'aio_phd/input/facebook/fb_part_3_of_4.csv',
            'aio_phd/input/facebook/fb_part_4_of_4.csv'];

$destinationObject = 'aio_phd/input/facebook/facebook_all_2.csv';

foreach ($objects as $sourceObject){
    $helpers->compose_two_files_storage($extractions, $destinationObject, $sourceObject);
}

