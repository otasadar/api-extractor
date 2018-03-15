<?php

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');

include __DIR__ . '/api/helpers.php';
include __DIR__ . '/api/adwords.php';
include __DIR__ . '/api/dcm.php';
include __DIR__ . '/api/dbm.php';
include __DIR__ . '/api/facebook.php';
include __DIR__ . '/api/ga.php';

$helpers = new helpers();
$adwords = new adwords();
$dcm = new dcm();
$dbm = new dbm();
$facebook = new facebook();
$ga = new ga();

$extraction = $_POST['extraction'];
$extraction['csv_output'] = '';// Temporal container for reports
$extraction['reportsData'] = '';// Clone of accountData + extra information from API requests
$extraction['file_name_tpl'] = $extraction['file_name']; // Template for filename manipulations


// Google Sheet Log
$client_id = $extraction['global']['google']['client_id'];
$client_secret = $extraction['global']['google']['client_secret'];
$now = new DateTime();
$extraction['global']['google_sheet']['access_token'] = $helpers->get_access_token($client_id, $client_secret, $extraction['global']['google_sheet']['refresh_token']);
$extraction['global']['google_sheet']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
$extraction = $helpers->live_log($extraction, Array("Start Task {$extraction['api']} - {$extraction['extraction_name']}--------------------------------------"));

//$helpers->gae_log(LOG_DEBUG, "check json_request" . $extraction);



switch ($extraction['api_type']) {
    case "google":
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $extraction['access_token'] = $helpers->get_access_token($client_id, $client_secret, $extraction['refresh_token']);
        $now = new DateTime();
        $extraction['access_token_datetime'] = $now->format('Y-m-d H:i:s');
        break;

    case "facebook":
        break;

    default:
        $helpers->gae_log(LOG_DEBUG, 'Not API Type provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_group']);
        break;
}

switch ($extraction['api']) {

    case "adwords":

        // create file with header
        $extraction['csv_output'] = $extraction['metrics']."\n";
        $helpers->create_csv_file($extraction);

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
            /*
            if (isset($extraction['split_day_period'])) {
                $dates = split_dates($extraction['startDate'], $extraction['endDate']);
                foreach ($dates as $date) {
                    $extraction['task_name']
                    $extraction['startDate'] = $date['startDate'];
                    $extraction['endDate'] = $date['endDate'];
                    $account_data = $adwords->set_adwords_request($extraction);
                }
            } else {
                $account_data = $adwords->set_adwords_request($extraction);
            }
            */

            $extraction = $helpers->check_access_token($extraction);
            $account_data = $adwords->set_adwords_request($extraction);

            if (mb_strlen($account_data) > 1 ) {
                $extraction['csv_output'] = $account_data;
                $helpers->storage_insert_combine_delete($extraction);
                $result = "OK";
            } else {
                $result = "EMPTY";
            }

            $log_values = Array(
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $result,
                mb_strlen($account_data));
            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $helpers->live_log($extraction, $log_values);

        }

        break;

    case "dcm":

        // create file with header
        $extraction['csv_output'] = $extraction['report_header']."\n";
        $helpers->create_csv_file($extraction);

        // Payload modifications
        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->schedule->expirationDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->schedule->startDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->name = $extraction['extraction_name'].'';
        $extraction['json_request'] = json_encode($extraction['json_request']);
        $extraction['profileIds_validated'] = $dcm->get_profilesIds($extraction);

        // adjust accountData Array
        $extraction['accountsData'] = $dcm->merge_profileId_array($extraction['accountsData']);

        // First loop : get reportId and fileId
        foreach ($extraction['accountsData'] as $profileId => $accountData) {

            // profileId validation
            if (!in_array($profileId, $extraction['profileIds_validated'])) {
                $log_values = Array($profileId,null,null,null,"ERROR","Profile ID not found");
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
                continue;
            }

            foreach ($accountData as $row) {

                $extraction = $helpers->check_access_token($extraction);
                $extraction['current'] = $row;
                $extraction['current']['profileId'] = $profileId;

                // Report Type vars
                switch ($extraction['report_type']) {

                    //prepare request
                    case "STANDARD":
                        // advertiserId validations
                        $advertiserId = $row['advertiserId'];
                        $advertiserIdsValidator = $dcm->check_advertiserIds($profileId, $extraction);
                        if (!in_array($advertiserId, $advertiserIdsValidator)) {

                            $log_values = Array(
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "Advertiser Id not found");
                            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->live_log($extraction, $log_values);
                            continue;
                        }

                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        if (isset($extraction['json_request']->criteria->dateRange->endDate)) {
                            if ($extraction['json_request']->criteria->dateRange->endDate === 'YESTERDAY') {
                                $extraction['json_request']->criteria->dateRange->endDate = $extraction['global']['dcm']['yesterday'];
                            }
                        }
                        $extraction['json_request']->criteria->dimensionFilters[0]->dimensionName = "dfa:advertiser";
                        $extraction['json_request']->criteria->dimensionFilters[0]->id = $advertiserId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);
                        break;
                    case "FLOODLIGHT":
                        // floodlightConfigIds validation
                        $floodlightConfigId = $row['floodlightConfigId'];
                        $floodlightConfigIdsValidator = $dcm->check_floodlightConfigIds($profileId, $extraction);
                        if (!in_array($floodlightConfigId, $floodlightConfigIdsValidator)) {

                            $log_values = Array(
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "FloodConfigId not found");
                            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->live_log($extraction, $log_values);
                            continue;

                        }

                        // edit report json request dynamically
                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        if (isset($extraction['json_request']->floodlightCriteria->dateRange->endDate)) {
                            if ($extraction['json_request']->floodlightCriteria->dateRange->endDate === 'YESTERDAY') {
                                $extraction['json_request']->floodlightCriteria->dateRange->endDate = $extraction['global']['dcm']['yesterday'];
                            }
                        }
                        $extraction['json_request']->floodlightCriteria->floodlightConfigId->value = $floodlightConfigId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);
                        break;
                    case "CROSS_DIMENSION_REACH":
                        // advertiserId validations
                        $advertiserId = $row['advertiserId'];
                        $advertiserIdsValidator = $dcm->check_advertiserIds($profileId, $extraction);
                        if (!in_array($advertiserId, $advertiserIdsValidator)) {

                            $log_values = Array(
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "Advertiser Id not found");
                            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->live_log($extraction, $log_values);
                            continue;
                        }

                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        if (isset($extraction['json_request']->crossDimensionReachCriteria->dateRange->endDate)) {
                            if ($extraction['json_request']->crossDimensionReachCriteria->dateRange->endDate === 'YESTERDAY') {
                                $extraction['json_request']->crossDimensionReachCriteria->dateRange->endDate = $extraction['global']['dcm']['yesterday'];
                            }
                        }
                        $extraction['json_request']->crossDimensionReachCriteria->dimensionFilters[0]->id = $advertiserId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);
                        break;
                    default:
                        $helpers->gae_log(LOG_DEBUG, "Report Type not provided");
                        continue;
                        break;
                }

                // start pull data
                $extraction = $dcm->start($extraction, $profileId);
                if (isset($extraction['current']['error'])) continue;

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
                    $helpers->live_log($extraction, $log_values);
                    continue;
                }

                // exist content case
                $raw_data = $dcm->headers_cleaner($raw_data, $extraction, 'Campaign', true);
                if (mb_strlen($extraction['csv_output'] ) > 1 ) {
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
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }

        }

        break;

    case "dbm":


        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->reportDataStartTimeMs = strtotime($extraction['startDate']) * 1000;
        $extraction['json_request']->reportDataEndTimeMs = strtotime($extraction['endDate']) * 1000;
        $extraction['json_request']->metadata->title = $extraction['extraction_name'].'EOF';
        $extraction['json_request'] = json_encode($extraction['json_request']);

        $helpers->gae_log(LOG_DEBUG, "check json_request" . $extraction['json_request']);

        // First part : Set, Run and get queryID
        /////////////////////////////////////
        $extraction['current'] = '';
        $extraction = $helpers->check_access_token($extraction);
        $extraction = $dbm->start($extraction);
        if (isset($extraction['current']['error'])) die;
        $extraction = $helpers->live_log($extraction, array("START"));

        // Second part : Wait until URL is generated
        /////////////////////////
        $extraction = $helpers->check_access_token($extraction);
        $response = $dbm->ask_until_status_available($extraction);
        // todo add check and control if return error 500

        // error case
        if ($response === 'FAILED' || $response === 'CANCELLED') {

            $helpers->gae_log(LOG_DEBUG, "ERROR-".$response);
            $extraction = $helpers->live_log($extraction, array("ERROR", $response));

        } else {

            $report_url = $response ;

            // Save URL to file
            $extraction['csv_output'] = $response;
            $file_path ="{$extraction['extraction_group']}/input/{$extraction['api']}/url-{$extraction['extraction_name']}";
            $helpers->create_csv_file($extraction,$file_path);

            // Save live logging
            $fileSize = $helpers->get_curl_remote_file_size($report_url);
            $extraction['csv_size'] = $fileSize;
            $content_status = ($extraction['csv_size']) ?  'OK' : 'EMPTY';
            $extraction = $helpers->live_log($extraction, array($content_status, $helpers->bytesToMBytes($extraction['csv_size']), $report_url));

            // Get active taks
            $json_tasks = $helpers->get_current_tasks($extraction);
            $json_tasks = json_decode($json_tasks);
            $task_pattern = "{$extraction['api']}-{$extraction['extraction_group']}";
            $active_task = 0;
            foreach ($json_tasks->tasks as $task) {
                if (strpos($task->name, $task_pattern) !== false) {
                    $active_task++;
                }
            }
            $helpers->gae_log(LOG_DEBUG, "active-task:".$active_task);
            if ($active_task === 1) $helpers->save_urls_data_to_buckets($extraction);

        }




        break;

    case "facebook":

        $extraction['csv_output'] = $extraction['report_header']."\n";
        $helpers->create_csv_file($extraction);
        $async_report_ids = [];
        $async_account_ids = [];
        $async_account_name = [];
        $is_sync = true;

        foreach ($extraction['accountsData'] as $key => $account) {

            $extraction['current'] = $account;
            $account_info = $facebook->set_facebook_request($extraction);
            $account_data = $account_info[0];

            if ($account_info[1]){
                $is_sync = false;
                array_push($async_report_ids, $account_info[1]);
                array_push($async_account_ids, $extraction['current']['accountId']);
                array_push($async_account_name, $extraction['current']['accountName']);

            } else {
                $is_sync = true;
            }

            if ($is_sync) {

                $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], 'sync', "START");
                $extraction = $helpers->live_log($extraction, $log_values);

                if (mb_strlen($account_data) > 1 ) {
                    $extraction['csv_output'] = $account_data;
                    $helpers->storage_insert_combine_delete($extraction);
                    $result = "OK";
                } else {
                    $result = "EMPTY";
                }

                $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], 'sync', $result, mb_strlen($account_data));
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }
        }

        if (!empty($async_report_ids)) {

            foreach ($async_report_ids as $key => $report_id) {

                $extraction['current'] = array('accountId' => $async_account_ids[$key], 'accountName' => $async_account_name[$key]);
                $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], 'async', "START");
                $extraction = $helpers->live_log($extraction, $log_values);

                $account_data = $facebook->set_async_facebook_request($extraction, $report_id);

                if (mb_strlen($account_data) > 1 ) {
                    $extraction['csv_output'] = $account_data;
                    $helpers->storage_insert_combine_delete($extraction);
                    $result = "OK";
                } else {
                    $result = "EMPTY";
                }

                $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], 'async', $result, mb_strlen($account_data));
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }
        }
        break;

    case "ga":

        // create file with header

        $extraction['csv_output'] = $extraction['headers']."\n";
        $helpers->create_csv_file($extraction);

        foreach ($extraction['accountsData'] as $key => $account) {

            $extraction['current'] = $account;
            $extraction['current']['key'] = $key;


            // Start
            $log_values = Array(
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $extraction['report'],
                "START");

            $extraction = $helpers->result_log($extraction, $log_values);
            $extraction = $helpers->check_access_token($extraction);
            $account_data = $ga->set_ga_request($extraction, null);

            if (mb_strlen($account_data) > 1 ) {
                $extraction['csv_output'] = $account_data;
                $helpers->storage_insert_combine_delete($extraction);
                $result = "OK";
            } else {
                $result = "EMPTY";
            }

            $log_values = Array(
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $result,
                mb_strlen($account_data));
            syslog(LOG_DEBUG, json_encode($log_values));
            $extraction = $helpers->result_log($extraction, $log_values);

        }

        break;

    default:
        $helpers->gae_log(LOG_DEBUG, 'Not API Name provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_group']);
        break;
}














