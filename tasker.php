<?php
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');

echo "v2";

// remove set_curl_adwords() from google storage


// todo manage 404 api response
// todo manage FAIL response
// todo add message when timeout
// todo remove profiles id duplicates in config file
// todo move this api switch to task for mutliples task, require open csv files


use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

include __DIR__ . '/api/helpers.php';
include __DIR__ . '/api/adwords.php';
include __DIR__ . '/api/dcm.php';


$helpers = new helpers();
$adwords = new adwords();
$dcm = new dcm();

$extraction = $_POST['extraction'];
$extraction['extraction_id'] = $_POST['extraction_id'];
$extraction['csv_output'] = '';
$extraction['reportsData'] = '';
$extraction['file_name_tpl'] = $extraction['file_name'];
$skip_headers = 'false'; //todo move this variable


// Google Sheet Log
$client_id = $extraction['global']['google']['client_id'];
$client_secret = $extraction['global']['google']['client_secret'];
$now = new DateTime();
$extraction['global']['google_sheet']['access_token'] = $helpers->get_access_token($client_id, $client_secret, $extraction['global']['google_sheet']['refresh_token']);
$extraction['global']['google_sheet']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
$extraction = $helpers->result_log($extraction, Array("Start Task {$extraction['file_name']}--------------------------------------"));
//syslog(LOG_DEBUG, 'Extraction:' . json_encode($extraction));


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
        syslog(LOG_DEBUG, 'Not API Type provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_name']);
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
                $extraction['api'],
                $extraction['task_name'],
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $extraction['report'],
                "START",
                null);
            $extraction = $helpers->result_log($extraction, $log_values);

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
                $extraction['api'],
                $extraction['task_name'],
                $extraction['current']['accountId'],
                $extraction['current']['accountName'],
                $extraction['report'],
                $result,
                mb_strlen($account_data));
            syslog(LOG_DEBUG, json_encode($log_values));
            $extraction = $helpers->result_log($extraction, $log_values);

        }

        break;

    case "dcm":

        $i = 0;
        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->schedule->expirationDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->schedule->startDate = $extraction['global']['dcm']['today'];
        $extraction['json_request'] = json_encode($extraction['json_request']);
        $extraction['profileIds_validated'] = $dcm->get_profilesIds($extraction);
        // adjust accountData Array
        $extraction['accountsData'] = $dcm->merge_profileId_array($extraction['accountsData']);

        // First loop : get reportId and fileId
        foreach ($extraction['accountsData'] as $profileId => $accountData) {

            // profileId validation
            if (!in_array($profileId, $extraction['profileIds_validated'])) {

                $log_values = Array(
                    $extraction['api'],
                    $profileId,
                    '---',
                    '---',
                    $extraction['report_type'],
                    null,
                    null,
                    "ERROR",
                    "Profile ID not found",
                    null);
                syslog(LOG_DEBUG, json_encode($log_values));
                $extraction = $helpers->result_log($extraction, $log_values);

                continue;
            }

            foreach ($accountData as $row) {

                $extraction = $helpers->check_access_token($extraction);
                $extraction['current'] = $row;
                $extraction['current']['profileId'] = $profileId;

                // Report Type vars
                switch ($extraction['report_type']) {

                    case "STANDARD":
                        // advertiserId validations
                        $advertiserId = $row['advertiserId'];
                        $advertiserIdsValidator = $dcm->check_advertiserIds($profileId, $extraction);
                        if (!in_array($advertiserId, $advertiserIdsValidator)) {

                            $log_values = Array(
                                $extraction['api'],
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $extraction['report_type'],
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "Advertiser Id not found",
                                null);
                            syslog(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->result_log($extraction, $log_values);
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
                                $extraction['api'],
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $extraction['report_type'],
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "FloodConfigId not found",
                                null);
                            syslog(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->result_log($extraction, $log_values);
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
                                $extraction['api'],
                                $profileId,
                                $helpers->return_isset($row['advertiserId']),
                                $helpers->return_isset($row['floodlightConfigId']),
                                $extraction['report_type'],
                                $row['advertiserName'],
                                $row['networkName'],
                                "ERROR",
                                "Advertiser Id not found",
                                null);
                            syslog(LOG_DEBUG, json_encode($log_values));
                            $extraction = $helpers->result_log($extraction, $log_values);
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
                        syslog(LOG_DEBUG, "Report Type not provided");
                        continue;
                        break;
                }

                // start pull data
                $extraction = $dcm->start($extraction, $profileId);

            }

        }


        syslog(LOG_DEBUG, "check control reportsData" . json_encode($extraction['reportsData']));


        // Second loop : ask and wait for content
        foreach ($extraction['reportsData'] as $row) {

            $extraction = $helpers->check_access_token($extraction);
            $extraction['current'] = $row;

            if (isset($row['reportId']) && isset($row['fileId'])) {
                $raw_data = $dcm->ask_until_status_available($extraction);
                // error case
                if ($raw_data === 'FAILED' || $raw_data === 'CANCELLED') {
                    $log_values = Array(
                        $extraction['api'],
                        $helpers->return_isset($extraction['current']['profileId']),
                        $helpers->return_isset($extraction['current']['advertiserId']),
                        $helpers->return_isset($extraction['current']['floodlightConfigId']),
                        $extraction['report_type'],
                        $helpers->return_isset($extraction['current']['advertiserName']),
                        $helpers->return_isset($extraction['current']['networkName']),
                        "ERROR",
                        $raw_data,
                        null);
                    syslog(LOG_DEBUG, json_encode($log_values));
                    $helpers->result_log($extraction, $log_values);
                    continue;
                }

                // exist content case
                $extraction = $dcm->get_report_header($raw_data, $extraction, 'Campaign');
                $raw_data = $dcm->headers_cleaner($raw_data, $extraction, 'Campaign', true);
                $extraction = $dcm->preparing_csv_file($raw_data, $extraction);
            }

        }

        $helpers->create_csv_file($extraction);
        $bucket = $extraction['global']['storage_data']['bucket'];
        syslog(LOG_DEBUG, "Saving CSV to bucket : $bucket filename: {$extraction['file_name']}");

        break;

    default:
        syslog(LOG_DEBUG, 'Not API Name provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_name']);
        break;
}














