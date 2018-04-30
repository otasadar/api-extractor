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
        $helpers->gae_log(LOG_DEBUG, $extraction['split_day_period']);

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

        $extraction = $helpers->check_access_token($extraction);

        // create file without header - ds include own headers
        $extraction['csv_output'] = '';
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


        // Second loop : ask and wait for content
        foreach ($extraction['reportsData'] as $row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            if (isset($row['reportId'])) {
                $api_response = $ds->ask_until_status_available($extraction);

                $report_url = $api_response->files[0]->url;
                $report_size = (int)$api_response->files[0]->byteCount;
                // todo add check and control if return error 500
                $helpers->gae_log(LOG_DEBUG, "report_size:" . $report_size);

                if ($report_size > 1) {
                    $extraction['csv_output'] = $ds->report_data_from_url($api_response, $extraction);
                    $helpers->storage_insert_combine_delete($extraction);
                    $result = "OK";
                } else {
                    $result = "EMPTY";
                }

                $log_values = Array(
                    $extraction['current']['agencyId'],
                    $extraction['current']['agencyName'],
                    $result,
                    $report_size);
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }

        }

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
                */

                // Save live logging
                $fileSize = $helpers->get_curl_remote_file_size($report_url);
                $extraction['csv_size'] = $fileSize;
                $content_status = ($extraction['csv_size']) ? 'OK' : 'EMPTY';
                $extraction = $helpers->live_log($extraction, array($content_status, $helpers->bytesToMBytes($extraction['csv_size']), $report_url));

            }

        }

        // 3 - Transfer URLs to bucket
        $extraction = $helpers->save_google_url_data_to_bucket($extraction);

        /*
         DEPRECATED, just for VM case:


        // 3 - If this is last task of this APO, get files from URL and put in storage
        // Warning : If two task finish at same time this part could not be executed
        // Warning : last task detector is based ina beta API

        $json_tasks = $helpers->get_current_tasks($extraction);
        $json_tasks = json_decode($json_tasks);
        $task_pattern = "{$extraction['api']}-{$extraction['extraction_group']}";
        $active_task = 0;

        foreach ($json_tasks->tasks as $task) {
            if (strpos($task->name, $task_pattern) !== false) {
                $active_task++;
            }
        }
        $helpers->gae_log(LOG_DEBUG, "active-task:" . $active_task);

        if ($active_task === 1) {
            //$response = $helpers->actions_jobs_executor_vm($extraction, 'start');
            //$helpers->gae_log(LOG_DEBUG, 'start-vm-jobs-executor' . $response);
            //sleep(100);
            $extraction = $helpers->save_urls_data_to_buckets($extraction);
            //$helpers->actions_jobs_executor_vm($extraction, 'stop');
            //$helpers->gae_log(LOG_DEBUG, 'stop-vm-jobs-executor' . json_encode($response));

        }
        */

        break;

    case "facebook":

        $reports = [];
        $reports_running = [];
        $start_date = $extraction['startDate'];
        $end_date = $extraction['endDate'];
        $still_pending_reports = true;
        $include_header = true;

        foreach ($extraction['accountsData'] as $key => $account) {
            $first_date_split = true;
            $extraction['current'] = $account;
            $array_dates = $helpers->split_dates_equal($start_date, $end_date, $helpers->date_difference($start_date, $end_date, 'm'));

            for ($x = 0; $x < count($array_dates) - 1; $x++) {

                if ($first_date_split) {
                    $extraction['startDate'] = $array_dates[$x];
                    $first_date_split = false;

                } else {
                    $date = date_create($array_dates[$x]);
                    date_add($date, date_interval_create_from_date_string('1 day'));
                    $extraction['startDate'] = date_format($date, 'Y-m-d');
                }

                $extraction['endDate'] = $array_dates[$x + 1];
                $report_request_id = $facebook->set_facebook_request($extraction);

                if ($report_request_id !== 'error') {
                    $report_info = new stdClass();
                    $report_info->report_id = $report_request_id;
                    $report_info->account_id = $extraction['current']['accountId'];
                    $report_info->account_name = $extraction['current']['accountName'];
                    $report_info->date_range = $extraction['startDate'] . '/' . $extraction['endDate'];
                    array_push($reports, $report_info);
                    $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], $report_request_id, 'REPORT REQUESTED');

                } else {
                    $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], $report_request_id, 'REPORT ERROR REQUESTED');

                }
                $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->live_log($extraction, $log_values);
            }
        }


        if (!empty($reports)) {

            while ($still_pending_reports) {

                foreach ($reports as $key => $report) {

                    $account_data = $facebook->set_async_facebook_request($extraction, $report->report_id);

                    if ($account_data[0] === 'loading') {
                        $report_running_info = new stdClass();
                        $report_running_info->report_id = $report->report_id;
                        $report_running_info->account_id = $report->account_id;
                        $report_running_info->account_name = $report->account_name;
                        $report_running_info->date_range = $report->date_range . '/' . $report->endDate;
                        array_push($reports_running, $report_running_info);

                        $current_time = new DateTime(date('H:i:s'));
                        $minute = (int)$current_time->format('i');

                        if ($minute % 5 === 0) {
                            $log_values = Array($report->account_id, $report->account_name, $report->report_id, 'REPORT LOADING-> ' . $account_data[1] . '%');
                            $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->live_log($extraction, $log_values);
                        }
                    }

                    if ($account_data[0] === 'fail') {
                        array_push($async_report_ids_running, $account_data[1]);

                        $log_values = Array($report->account_id, $report->account_name, $report->report_id . ' ---> ' . $account_data[1], 'REPORT FAILED');
                        $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                        $extraction = $helpers->live_log($extraction, $log_values);
                    }

                    if ($account_data[0] === 'done') {

                        $log_values = Array($report->account_id, $report->account_name, $report->report_id, 'REPORT RECEIVED FROM API');
                        $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                        $extraction = $helpers->live_log($extraction, $log_values);

                        if (mb_strlen($account_data[1]) > 22) {

                            if ($include_header) {

                                // original version
                                //$extraction['csv_output'] = $account_data[1];

                                //version for edit header
                                //$extraction['csv_output'] = $facebook->add_pattern_to_header ($account_data[1] , 'fb_');


                                $header = implode(array_slice(explode("\n", $account_data[1]), 0,1));
                                $header = preg_replace('/[^,_A-Za-z0-9\-]/', "", $header);
                                $header = 'fb_' . str_replace(',',',fb_',$header);
                                $helpers->gae_log(LOG_DEBUG, $header);

                                $header = explode(",",$header);
                                $helpers->gae_log(LOG_DEBUG, json_encode($header));

                                $header_string_unique = implode(",", $helpers->unique_arr($header));
                                $helpers->gae_log(LOG_DEBUG, $header_string_unique);


                                $body = implode("\n",array_slice(explode("\n", $account_data[1]), 1));
                                $extraction['csv_output'] = $header_string_unique . "\n" . $body;


                                $helpers->create_csv_file($extraction);
                                $include_header = false;
                                $result = 'REPORT UPDATED TO STORAGE WITH HEADER';

                            } else {
                                $extraction['csv_output'] = implode("\n", array_slice(explode("\n", $account_data[1]), 1));
                                $helpers->storage_insert_combine_delete($extraction);
                                $result = 'REPORT UPDATED TO STORAGE WITHOUT HEADER';
                            }

                        } else {
                            $result = 'REPORT IS EMPTY';
                        }

                        $log_values = Array($report->account_id, $report->account_name, $report->report_id, $result, mb_strlen($account_data[1]));
                        $helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                        $extraction = $helpers->live_log($extraction, $log_values);
                    }
                }

                if (!empty($async_report_ids_running)) {
                    $reports = $reports_running;
                    $reports_running = [];
                    sleep(60);
                } else {
                    $still_pending_reports = false;
                }
            }
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
$extraction['global']['google_sheet']['last_request'] = date("Y-m-d H:i:s", strtotime(date("Y-m-d H:i:s", strtotime($extraction['global']['google_sheet']['last_request'])) . " -1 day"));
$extraction = $helpers->live_log($extraction, Array("End Task {$extraction['api']} - {$extraction['extraction_name']}--------------------------------------"));











