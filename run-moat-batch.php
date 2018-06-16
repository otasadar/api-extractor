<?php
/*
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
///
/// 12/6/2018
/// 2018-05-01 2018-05-02
/// 2018-05-03 2018-05-15
/// 2018-05-16 2018-05-31
/// 2018-06-01 2018-06-11
///
/// /// 2018-04-01 2018-04-30
///
/// 2018-04-12
/// 2018-04-27 to 30
for ($x = 27; $x <= 30; $x++) {

    if($x < 10) $x = "0$x"; // add zeros to day number
    $date = "2018-04-$x";

    // Main vars
    $path = "sftp://52.87.195.145:22/mnt/accounts/phd_unilever_mena_moat/log_files/phd_unilever_mena_moat_logs_$date.csv.gz";


    // Send data to VM
    $endpoint = 'http://35.200.161.162/helpers/ftp-to-bucket.php';
    $bucket = $extractions['global']['google_storage']['bucket'];
    $new_file = "$date";
    $final_object = "moat/moat-$date.csv";
    $payload = "bucket=$bucket&storage_access_token=$storage_access_token&ftp_server=$ftp_server&ftp_user=$ftp_user&ftp_pass=$ftp_pass&path=$path&new_file=$new_file&final_object=$final_object";
    $response = $helpers->set_curl('', $endpoint, $payload, 'POST');
    $helpers->gae_log(LOG_DEBUG, "ftp-to-bucket response:" . json_encode($response));




    // BigQuery
    $extractions['schema'] = '"schema": {
    "fields": [
        {"name": "Level1ID","type": "INTEGER"},
        {"name": "Level2ID","type": "INTEGER"},
        {"name": "Level3ID","type": "INTEGER"},
        {"name": "Level4ID","type": "INTEGER"},
        {"name": "Slicer1ID","type": "STRING"},
        {"name": "Slicer2ID","type": "STRING"},
        {"name": "Domain","type": "STRING"},
        {"name": "Hover","type": "BOOLEAN"},
        {"name": "InView_Viewable","type": "BOOLEAN"},
        {"name": "InView_Measurable","type": "BOOLEAN"},
        {"name": "Ord","type": "INTEGER"},
        {"name": "Random","type": "INTEGER"},
        {"name": "BelowFoldAd","type": "INTEGER"},
        {"name": "ActivePageDwell","type": "INTEGER"},
        {"name": "Scroll","type": "INTEGER"},
        {"name": "TimeUntilInView","type": "INTEGER"},
        {"name": "TimeUntilHover","type": "INTEGER"},
        {"name": "TimeUntilScroll","type": "INTEGER"},
        {"name": "TotalExposureTime","type": "INTEGER"},
        {"name": "UniversalInteractionTime","type": "INTEGER"},
        {"name": "UniversalInteraction","type": "BOOLEAN"},
        {"name": "FullInView","type": "INTEGER"},
        {"name": "PixelType","type": "STRING"},
        {"name": "Was_Any_Pixel_Onscreen","type": "BOOLEAN"},
        {"name": "_2_sec_fully_on_screen_impression","type": "BOOLEAN"},
        {"name": "_80__on_screen_for_one_metric","type": "BOOLEAN"},
        {"name": "active_inview_time","type": "INTEGER"},
        {"name": "Automated_Browser","type": "BOOLEAN"},
        {"name": "Data_Center_Traffic","type": "BOOLEAN"},
        {"name": "Incongruous_Browser","type": "BOOLEAN"},
        {"name": "Late_Night_Traffic","type": "BOOLEAN"},
        {"name": "NHT","type": "BOOLEAN"},
        {"name": "NHT_Measurable","type": "BOOLEAN"},
        {"name": "Non_US_Traffic","type": "BOOLEAN"},
        {"name": "Old_Browser","type": "BOOLEAN"},
        {"name": "Proxy_Traffic","type": "BOOLEAN"},
        {"name": "Top_of_the_Hour_Traffic","type": "BOOLEAN"}
        ]
      },
      "nullMarker" : "null",
      "maxBadRecords" : 9999999,
      "allowJaggedRows" : true,
      "ignoreUnknownValues" : true,
      "skipLeadingRows" : 1';

    $extractions['projectId'] = 'annalect-api-jobs';
    $extractions['datasetId'] = 'moat';
    $extractions['tableId'] = 'yesterday';
    $extractions['object'] = "moat/moat-$date.csv";
    $extractions['disposition'] = 'truncate'; // truncate - append - empty
    $helpers->upload_big_query($extractions);

    // PART 3
    // Select from tmp table and put in the main table adding date

    sleep(30); // wait for table update

    $extractions['projectId'] = 'annalect-api-jobs';
    $extractions['datasetId'] = 'moat';
    $extractions['tableId'] = 'main';

    $extractions['query'] = "SELECT CAST('$date' AS DATE) AS Date, * FROM [annalect-api-jobs:moat.yesterday]";
    $extractions['disposition'] = 'append'; // truncate - append - empty
    $helpers->select_big_query($extractions);

}


// Stop VM
$response = $helpers->actions_jobs_executor_vm($extractions, 'stop');
$helpers->gae_log(LOG_DEBUG, 'stop-vm-jobs-executor' . json_encode($response));
*/

?>
