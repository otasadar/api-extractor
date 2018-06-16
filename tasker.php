<?php

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');

include __DIR__ . '/api/helpers.php';
include __DIR__ . '/api/adwords.php';
include __DIR__ . '/api/dcm.php';
include __DIR__ . '/api/dbm.php';
include __DIR__ . '/api/ds.php';
include __DIR__ . '/api/facebook.php';
include __DIR__ . '/api/ga.php';
include __DIR__ . '/api/yandex.php';

$helpers = new helpers();
$adwords = new adwords();
$dcm = new dcm();
$dbm = new dbm();
$ds = new ds();
$facebook = new facebook();
$ga = new ga();
$yandex = new yandex();

$extraction = $helpers->init_extraction($_POST['extraction']);


switch ($extraction['api']) {

    case "adwords":
        $extraction = $helpers->check_access_token($extraction);

        // create file with header
        $extraction['csv_output'] = $extraction['metrics'] . "\n";
        $helpers->create_csv_file($extraction);

        $helpers->gae_log(LOG_DEBUG, $extraction['startDate']);
        $helpers->gae_log(LOG_DEBUG, $extraction['endDate']);

        if (isset($extraction['split_day_period'])) {
            $dates = $helpers->split_dates($extraction['startDate'], $extraction['endDate'], $extraction['split_day_period']);
            $helpers->gae_log(LOG_DEBUG, "all dates".json_encode($dates));
        }


        foreach ($extraction['accountsData'] as $key => $account) {

            $extraction['current'] = $account;
            $extraction['current']['key'] = $key;


            // Start
            $log_values = Array(
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                "START");
            $extraction = $helpers->live_log($extraction, $log_values);

            // Split by dates
            if (isset($extraction['split_day_period'])) {

                $report_data = '';
                foreach ($dates as $date) {
                    $extraction['startDate'] = $date['startDate'];
                    $extraction['endDate'] = $date['endDate'];
                    $extraction = $helpers->check_access_token($extraction);
                    $report_data .= $adwords->set_adwords_request($extraction);
                }

            } else {
                // One extractions per ID
                $extraction = $helpers->check_access_token($extraction);
                $report_data = $adwords->set_adwords_request($extraction);
            }

            if (mb_strlen($report_data) > 1) {
                $extraction['csv_output'] = $report_data;
                $helpers->storage_insert_combine_delete($extraction);
                $result = "OK";
            } else {
                $result = "EMPTY";
            }

            $log_values = Array(
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $result,
                mb_strlen($report_data));
            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $helpers->live_log($extraction, $log_values);

        }

        break;

    case "ds":

        // Start VM - DS URL transfer require it
        $helpers->start_vm($extraction);


        // Create empty file for append tmp
        $helpers->create_csv_file($extraction);

        // First loop : get reportId and fileId
        foreach ($extraction['accountsData'] as $row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            $log_values = Array(
                $extraction['current']['agencyId'],
                $extraction['current']['agencyName'],
                "START");
            $extraction = $helpers->live_log($extraction, $log_values);

            $extraction['json_request'] = json_decode($extraction['json_request']);
            $extraction['json_request']->timeRange->startDate = $extraction['startDate'];
            $extraction['json_request']->timeRange->endDate = $extraction['endDate'];
            $extraction['json_request']->reportScope->agencyId = $row['agencyId'];
            $extraction['json_request'] = json_encode($extraction['json_request']);
            // start pull data
            $extraction = $ds->start($extraction);
            if (isset($extraction['current']['error'])) continue;

        }

        $helpers->gae_log(LOG_DEBUG, "check control reportsData" . json_encode($extraction['reportsData']));


        // 2 - Second loop : ask and wait for content
        foreach ($extraction['reportsData'] as $row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            if (isset($row['reportId'])) {
                $api_response = $ds->ask_until_status_available($extraction);
                // todo add check and control if return error 500

                $report_url = $api_response->files[0]->url;
                $report_size = (int)$api_response->files[0]->byteCount;

                // case REPORT DONE
                $extraction['reportUrls'][] = $report_url;


                // Save live logging
                $content_status = ($report_size) ? 'OK' : 'EMPTY';
                $extraction = $helpers->check_access_token($extraction);
                $extraction = $helpers->live_log($extraction, array($content_status, $helpers->bytesToMBytes($report_size), $report_url, $extraction['access_token']));

                // Save tmp url file
                //$bucket = $extraction['global']['google_storage']['bucket'];
                //$file_path = "gs://$bucket/{$extraction['extraction_group']}/input/{$extraction['api']}/url-{$extraction['extraction_name']}-{$row['reportId']}";
                //file_put_contents($file_path, $report_url);
                $helpers->gae_log(LOG_DEBUG, "check url" . $report_url);

                $extraction = $helpers->save_urls_data_to_buckets($extraction, $report_url);

            }

        }

        // If two task are active at same will not work
        // Close VM in cronjobs
        //$extraction =  $helpers->stop_vm_if_last_task_of_this_api($extraction);
        break;

    case "dcm":

        $extraction = $helpers->check_access_token($extraction);

        // create file with header
        $extraction['csv_output'] = $extraction['report_header'] . "\n";
        $helpers->create_csv_file($extraction);

        // Payload modifications
        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->schedule->expirationDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->schedule->startDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->name = $extraction['extraction_name'] . '';
        $extraction['json_request'] = json_encode($extraction['json_request']);
        $extraction['profileIds_validated'] = $dcm->get_profilesIds($extraction);

        // adjust accountData Array
        $extraction['accountsData'] = $dcm->merge_profileId_array($extraction['accountsData']);

        // First loop : get reportId and fileId
        foreach ($extraction['accountsData'] as $profileId => $accountData) {

            // profileId validation

            if (!in_array($profileId, $extraction['profileIds_validated'])) {
                $log_values = Array($profileId, null, null, null, "ERROR", "Profile ID not found");
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
                continue;
            }


            foreach ($accountData as $row) {

                $extraction = $helpers->check_access_token($extraction);
                $extraction['current'] = $row;
                $extraction['current']['profileId'] = $profileId;

                // Manipulation payload request according report type
                $extraction = $dcm->preparing_report_type_vars($extraction);
                if (isset($extraction['current']['error'])) {
                    unset ($extraction['current']['error']);
                    continue;
                }

                // Start pull data and fill reportsData
                $extraction = $dcm->start($extraction, $profileId);
                if (isset($extraction['current']['error'])) {
                    unset ($extraction['current']['error']);
                    continue;
                }

            }

        }

        $helpers->gae_log(LOG_DEBUG, "check control reportsData" . json_encode($extraction['reportsData']));


        // Second loop : ask and wait for content
        foreach ($extraction['reportsData'] as $row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            if (isset($row['reportId']) && isset($row['fileId'])) {
                $raw_data = $dcm->ask_until_status_available($extraction);
                // todo add check and control if return error 500

                // error case
                if ($raw_data === 'FAILED' || $raw_data === 'CANCELLED') {
                    $log_values = Array(
                        $helpers->return_isset($extraction['current']['profileId']),
                        $helpers->return_isset($extraction['current']['advertiserId']),
                        $helpers->return_isset($extraction['current']['floodlightConfigId']),
                        $helpers->return_isset($extraction['current']['advertiserName']),
                        $helpers->return_isset($extraction['current']['networkName']),
                        "ERROR",
                        $raw_data);
                    $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                    $extraction = $helpers->live_log($extraction, $log_values);
                    continue;
                }

                // exist content case
                $raw_data = $dcm->headers_cleaner($raw_data, $extraction, 'Campaign', true);
                if (mb_strlen($raw_data) > 1) {
                    $extraction['csv_output'] = $raw_data;
                    $helpers->storage_insert_combine_delete($extraction);
                    $result = "OK";
                } else {
                    $result = "EMPTY";
                }

                $log_values = Array(
                    $extraction['current']['profileId'],
                    $helpers->return_isset($extraction['current']['advertiserId']),
                    $helpers->return_isset($extraction['current']['floodlightConfigId']),
                    $helpers->return_isset($extraction['current']['advertiserName']),
                    $helpers->return_isset($extraction['current']['networkName']),
                    $result,
                    mb_strlen($raw_data));
                $helpers->gae_log(LOG_DEBUG, "REPORT OK:" . json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }

        }

        break;

    case "dbm":

        // Start VM - DS URL transfer require it
        $helpers->start_vm($extraction);

        // Create empty file for append tmp
        $helpers->create_csv_file($extraction);

        // 1 - First loop : accountsData to reportsData with queryId
        foreach ($extraction['accountsData'] as $accountData) {

            $extraction['current'] = $accountData;

            $extraction = $dbm->preparing_payload($extraction);
            $extraction = $helpers->check_access_token($extraction);

            // start pull data and fill reportsData
            $extraction = $dbm->start($extraction);
            if (isset($extraction['current']['error'])) {
                 $extraction = $helpers->reset_error($extraction);
                 continue;
            }

        }

        $helpers->gae_log(LOG_DEBUG, "check control reportsData" . json_encode($extraction['reportsData']));

        // 2 - Second loop : Wait until URL is generated
        foreach ($extraction['reportsData'] as $key =>$row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            if (isset($row['queryId'])) {
                $extraction = $dbm->ask_until_status_available($extraction);
                $response = $extraction['current']['response'];

                // error with response 200
                if ($response === 'FAILED' || $response === 'CANCELLED') {
                    $helpers->gae_log(LOG_DEBUG, "ERROR-" . $response);
                    $extraction = $helpers->live_log($extraction, array("ERROR", $response));
                    continue;
                }

                // error with response ! 200
                if (isset($extraction['current']['error'])) {
                    $extraction = $helpers->reset_error($extraction);
                    continue;
                }


                // case REPORT DONE
                $report_url = $response;
                $extraction['reportUrls'][] = $report_url;

                // Save URL to file - Deprecated, just for VM case
                /*
                $extraction['csv_output'] = $response;
                $file_path = "{$extraction['extraction_group']}/input/{$extraction['api']}/url-{$extraction['extraction_name']}-{$row['queryId']}";
                $helpers->create_csv_file($extraction, $file_path);

                partnerId' => '407', 'partnerName
                */

                // Save live logging
                $fileSize = $helpers->get_curl_remote_file_size($report_url);
                $extraction['csv_size'] = $fileSize;
                $content_status = ($extraction['csv_size']) ? 'OK' : 'EMPTY';
                $extraction = $helpers->live_log($extraction, array($content_status,
                    $helpers->bytesToMBytes($extraction['csv_size']),
                    $report_url,
                    $extraction['current']['partnerId'],
                    $extraction['current']['partnerName']));

                // Save URL to bucket - From VM - Download, Upload & delete file
                $extraction = $helpers->save_urls_data_to_buckets($extraction, $report_url);


            }

        }

        // 3 - Transfer URLs to bucket
        //$extraction = $helpers->save_dbm_url_data_to_bucket($extraction);



        break;

    case "facebook":

        // Create dates intervals
        if (isset($extraction['split_day_period'])) {
            $dates = $helpers->split_dates($extraction['startDate'], $extraction['endDate'], $extraction['split_day_period'], 'Y-m-d');
            $helpers->gae_log(LOG_DEBUG, "all dates".json_encode($dates));
        }

        // First loop to init reports requests
        $i = 0;
        foreach ($extraction['accountsData'] as $key => $account) {
            $helpers->print_first_extraction_for_testing($extraction, $i); $i++;
            $extraction['current'] = $account;
            // Split by dates
            if (isset($extraction['split_day_period'])) {
                foreach ($dates as $date) {
                    $extraction['startDate'] = $date['startDate'];
                    $extraction['endDate'] = $date['endDate'];
                    $extraction = $facebook->init_facebook_report_request ($extraction);
                }

            } else {
                // One extractions per ID
                $extraction = $facebook->init_facebook_report_request ($extraction);
            }
        }

        // Second loop wait for status and save data
        foreach ($extraction['reportsData'] as $key =>$row) {
            $extraction['current'] = $row;
            $extraction['current']['attempt'] = 1;
            $extraction = $facebook->wait_until_status_done ($extraction);
            $extraction = $facebook->save_report_data ($extraction);
        }

        break;

    case "ga":

        // create file with header

        $extraction['csv_output'] = $extraction['headers'] . "\n";
        $helpers->create_csv_file($extraction);

        foreach ($extraction['accountsData'] as $key => $account) {

            $extraction['current'] = $account;
            $extraction['current']['key'] = $key;


            // Start
            $log_values = Array(
                $extraction['current']['viewId'],
                $extraction['current']['viewName'],
                "START");

            $extraction = $helpers->live_log($extraction, $log_values);
            $extraction = $helpers->check_access_token($extraction);
            $account_data = $ga->set_ga_request($extraction, null);

            if (mb_strlen($account_data) > 1) {
                $extraction['csv_output'] = $account_data;
                $helpers->storage_insert_combine_delete($extraction);
                $result = "OK";
            } else {
                $result = "EMPTY";
            }

            $log_values = Array(
                $extraction['current']['viewId'],
                $extraction['current']['viewName'],
                $result,
                mb_strlen($account_data));
            syslog(LOG_DEBUG, json_encode($log_values));
            $extraction = $helpers->live_log($extraction, $log_values);

        }

        break;

    case "yandex":

        // create file with header
        $extraction['csv_output'] = $extraction['report_header'] . "\n";
        $helpers->create_csv_file($extraction);

        foreach ($extraction['accountsData'] as $key => $account) {

            $extraction['current'] = $account;

            $extraction['json_request'] = json_decode($extraction['json_request']);
            $extraction['json_request']->params->SelectionCriteria->DateFrom = $extraction['startDate'];
            $extraction['json_request']->params->SelectionCriteria->DateTo = $extraction['endDate'];
            $extraction['current']['ReportName'] = $extraction['json_request']->params->ReportName . rand();
            $extraction['json_request']->params->ReportName = $extraction['current'] ['ReportName'];
            $extraction['json_request'] = json_encode($extraction['json_request']);

            $log_values = Array($extraction['current']['clientLogin'], "START");
            $extraction = $helpers->live_log($extraction, $log_values);
            $extraction = $yandex->set_yandex_request($extraction);

            syslog(LOG_DEBUG, "output size:" . mb_strlen($extraction['csv_output']));


            if (mb_strlen($extraction['csv_output']) > 1) {
                $helpers->storage_insert_combine_delete($extraction);
                $result = "OK";
            } else {
                $result = "EMPTY";
            }

            $log_values = Array($extraction['current']['clientLogin'], $result, mb_strlen($extraction['csv_output']));
            syslog(LOG_DEBUG, "test last part:" . json_encode($log_values));
            $extraction = $helpers->live_log($extraction, $log_values);
        }
        break;

    default:
        $helpers->gae_log(LOG_DEBUG, 'Not API Name provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_group']);
        break;

}



$extraction = $helpers->rename_tmp_to_final_file($extraction);

// Force last row live log
$helpers->live_log_instant($extraction, Array("End Task {$extraction['api']} - {$extraction['extraction_name']}--------------------------------------"));












