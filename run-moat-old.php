<?php

// MOAT extractions

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();

eval($helpers->init_global_config());



// Main vars
$storage_access_token = $helpers->get_storage_access_token($extractions);
$ftp_server = 'moatads.com';
$ftp_user = 'phd_unilever_mena_moat';
$ftp_pass = 'FC0UnJ5fgdjW6sW/XwtTkoO85RncmG14zzdkuh2UARV8xNmiCpfVAA==';


// Start VM
$response = $helpers->actions_jobs_executor_vm($extractions, 'start');
$helpers->gae_log(LOG_DEBUG, 'start-vm-jobs-executor' . json_encode($response));
sleep(30);




/// batch process
for ($x = 1; $x <= 2; $x++) {

    if($x < 10) $x = "0$x";
    $date = "2018-05-$x";

    // Main vars
    $path = "sftp://52.87.195.145:22/mnt/accounts/phd_unilever_mena_moat/log_files/phd_unilever_mena_moat_logs_$date.csv.gz";


    // Send data to VM
    $endpoint = 'http://35.200.161.162/helpers/ftp-to-bucket-old.php';
    $bucket = $extractions['global']['google_storage']['bucket'];
    $new_file = "$date";
    $final_object = "moat/moat-$date.csv";
    $payload = "bucket=$bucket&storage_access_token=$storage_access_token&ftp_server=$ftp_server&ftp_user=$ftp_user&ftp_pass=$ftp_pass&path=$path&new_file=$new_file&final_object=$final_object";
    $response = $helpers->set_curl('', $endpoint, $payload, 'POST');
    $helpers->gae_log(LOG_DEBUG, "ftp-to-bucket response:" . json_encode($response));


    // BigQuery
    $extractions['projectId'] = 'annalect-api-jobs';
    $extractions['datasetId'] = 'moat';
    $extractions['tableId'] = 'test3';
    $extractions['object'] = "moat/moat-$date.csv";
    $extractions['disposition'] = 'append';
    $helpers->upload_big_query($extractions);
}


// Stop VM
$response = $helpers->actions_jobs_executor_vm($extractions, 'stop');
$helpers->gae_log(LOG_DEBUG, 'stop-vm-jobs-executor' . json_encode($response));


?>
