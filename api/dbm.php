<?php

/**
 * DBM Functions
 */

include_once  __DIR__ . '/helpers.php';


class dbm
{


    private $helpers;
    public function __construct()
    {
        $this->helpers = new helpers();
    }


    /**
     * DBM API Report Requests:
     */

    // DBM First Function for start a combo of DBM request
    function start($extraction)
    {
        // to do add checkers & retries
        $api_response = $this->report_setup($extraction);
        $extraction = $this->helpers->check_n_control($api_response, $extraction);
        if (isset($extraction['current']['error'])) return $extraction;

        $extraction['current']['queryId'] = $api_response->queryId;
        $extraction['reportsData'][] = $extraction['current'];

        return $extraction;

    }

    // DBM request 1 - Setup report and get report query id
    function report_setup($extraction)
    {

        $headers = array("Authorization: Bearer {$extraction['access_token']}" , 'Accept: application/json', 'Content-Type: application/json' );
        $api_version = $extraction['global']['dbm']['api_version'];
        $endpoint = "https://www.googleapis.com/doubleclickbidmanager/$api_version/query";

        $curl_response = $this->helpers->set_curl($headers, $endpoint, $extraction['json_request'], 'POST', null);
        if (is_array($curl_response)) {
            return $curl_response; // error
        } else {
            sleep(1);
            return json_decode($curl_response);
        }
    }

    // DBM request 2 - Run report for get URL
    function run_report($api_response, $extraction)
    {

        $queryId= $api_response->queryId;
        $api_version = $extraction['global']['dbm']['api_version'];
        $endpoint = "https://www.googleapis.com/doubleclickbidmanager/$api_version/query/$queryId";
        // todo add dynamic from original query
        // todo check if next var is required, maybe can be deleted
        $request_body = '{
          "dataRange": "CUSTOM_DATES",
          "reportDataStartTimeMs": "1496260800000",
          "reportDataEndTimeMs": "1519502400000",
          "timezoneCode": "Asia/Dubai"
        }';
        $headers = array("Authorization: Bearer {$extraction['access_token']}" , 'Accept: application/json', 'Content-Type: application/json' );
        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'POST', null);
        // Run report return am empty 204 response
        //$curl_response = json_decode($curl_response);

        $this->helpers->gae_log(LOG_DEBUG, "dbm_run_report ");
        sleep(1);
        return $curl_response;
    }

    // DBM request 3 - Check if media report file is generated
    function get_report_url($extraction)
    {

        $queryId= $extraction['current']['queryId'];
        $headers = array("Authorization: Bearer {$extraction['access_token']}" , 'Accept: application/json' );
        $api_version = $extraction['global']['dbm']['api_version'];
        $endpoint = "https://www.googleapis.com/doubleclickbidmanager/$api_version/queries/$queryId/reports";
        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);


        $extraction = $this->helpers->check_json_response($curl_response, $extraction);
        $this->helpers->gae_log(LOG_DEBUG, "dbm_get_report_url " . json_encode($curl_response));
        sleep(1);
        if ($this->helpers->check_for_retries($extraction) ) return $this->get_report_url($extraction);

        return $extraction;
    }


    // DBM recursive functions for get report data
    function ask_until_status_available($extraction)
    {
        $extraction = $this->helpers->check_access_token($extraction);
        $extraction = $this->get_report_url($extraction);


        if (isset($extraction['current']['error']))  {
            $status = $extraction['current']['http_code'];
        } else {
            $response = $extraction['current']['response'];
            $status = $response->reports[0]->metadata->status->state;

        }
        $extraction = $this->helpers->live_log($extraction, Array("STATUS", $status));


        if ($status=== "DONE") {
            $extraction['current']['response'] = $response->reports[0]->metadata->googleCloudStoragePath;
            return $extraction;

        } else if ($status === "RUNNING") {
            $this->helpers->gae_log(LOG_DEBUG, "queueDelay:60");
            sleep(60);
            return $this->ask_until_status_available($extraction);

        } else {
            $extraction['current']['response'] = $status;
            return $extraction;
        }
    }




    /**
     * DBM Helpers:
     */

    // Merge Duplicate's Ids in a single array with all sub id and other data
    function merge_partnerId_array($original_array)
    {

        /*
        $arr1 = array(
            array('partnerId' => '2896506', 'advertiserId' => '6197584'),
            array('partnerId' => '2896506', 'advertiserId' => '6203268'),
            array('partnerId' => '2896506', 'advertiserId' => '6203788'),
            array('partnerId' => '2896506', 'advertiserId' => '6203270'),
            array('partnerId' => '2896506', 'advertiserId' => '6199969'),
            array('partnerId' => '2719739', 'advertiserId' => '5912534'),

            array('partnerId' => '2719829', 'advertiserId' => '8271328'),
            array('partnerId' => '2719829', 'advertiserId' => '5452626'),
            array('partnerId' => '2719829', 'advertiserId' => '5449796'),
            array('partnerId' => '2719829', 'advertiserId' => '5454647'),
        );
        */

        $outer_array = array();
        $unique_array = array();


        foreach ($original_array as $key => $value) {
            $inner_array = array();

            $partnerId_value = $value['partnerId'];
            if (!in_array($value['partnerId'], $unique_array)) {
                array_push($unique_array, $partnerId_value);

                unset($value['partnerId']);
                array_push($inner_array, $value);
                $outer_array[$partnerId_value] = $inner_array;


            } else {
                unset($value['partnerId']);
                array_push($outer_array[$partnerId_value], $value);

            }
        }

        return $outer_array;

    }

    // Clean header
    function header_cleaner($raw_data)
    {
        if (empty($raw_data)) {
            return $raw_data;
        } else {
            $rows = explode("\n", $raw_data);
            unset($rows[0]);
            $filter_data = implode("\n", $rows);
            return $filter_data . "\n";
        }

    }

    // Clean footer
    function footer_cleaner($raw_data, $needle)
    {
        if (empty($raw_data)) {
            return $raw_data;
        } else {
            $rows = explode("\n", $raw_data);

            $total_rows = count($rows);
            $footer_reference = null;

            //found footer reference
            if (isset($needle)) {
                for ($i = 0; $i < $total_rows; $i++) {
                    if (strpos($rows[$i], $needle) !== false) {
                        $footer_reference = $i;
                    }
                }
                if (!$footer_reference) {
                    $this->helpers->gae_log(LOG_DEBUG, "needle not exists:" . $raw_data);
                }
            } else {
                $this->helpers->gae_log(LOG_DEBUG, "needle not defined:" . $raw_data);
            }

            // remove from reference to the end
            for ($i = $footer_reference; $i < $total_rows; $i++) {
                unset($rows[$i]);
            }

            // remove previous line from reference
            unset($rows[($i-1)]);
            unset($rows[($i-2)]);


            $filter_data = implode("\n", $rows);
            return $filter_data . "\n";
        }

    }

    // Combine results of queries
    function check_and_append_to_file($response, $extraction, $needle)
    {

        $fileSize = $this->helpers->get_curl_remote_file_size($response);
        $portion = 100000000; //100 Mb
        $i = 0;
        $ranges = [];
        $content_status = "extracting";

        while($i <= $fileSize) {
            $start = $i;
            $i = $i + $portion;
            $end = $i - 1;
            $ranges[] = "$start-$end";
        }



        foreach ($ranges as $key => $range) {
            $raw_data = $this->helpers->set_simple_curl($response,$range);
            $current_ext = $key+1;

            $log_values = Array(
                $extraction['api'],
                $extraction['task_name'],
                $extraction['report_type'],
                "DOWNLOAD-RANGE-$current_ext-START",
                $this->helpers->bytesToMBytes(strlen($raw_data)));
            $extraction = $this->helpers->live_log($extraction, $log_values);


            //clean header and empty result
            if ($key === 0) {

                // empty validation
                if (strpos($raw_data, 'No data returned by the reporting service') ) {
                    $extraction['csv_size'] = 0;
                } else {
                    $extraction['csv_size'] = $this->helpers->bytesToMBytes($fileSize);
                    $raw_data = $this->header_cleaner($raw_data);
                }

            }

            //clean footer
            if (($key +1) === count($ranges)) {
                $raw_data = $this->footer_cleaner($raw_data, $needle);
            }

            // process data
            $extraction['csv_output'] = $raw_data;
            $this->helpers->storage_insert_combine_delete($extraction);

            $log_values = Array(
                $extraction['api'],
                $extraction['task_name'],
                $extraction['report_type'],
                "DOWNLOAD-RANGE-$current_ext-END",
                $this->helpers->bytesToMBytes(strlen($raw_data)));
            $extraction = $this->helpers->live_log($extraction, $log_values);

        }

        return $extraction;
    }

    // Preparing payload request
    function preparing_payload ($extraction) {

        // prepare payload
        if (!isset($extraction['refresh_token'])) {
            $extraction['refresh_token'] = $extraction['current']['refreshToken'];
        }
        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->params->filters[0]->value =  $extraction['current']['partnerId'];
        $extraction['json_request']->reportDataStartTimeMs = strtotime($extraction['startDate']) * 1000;
        $extraction['json_request']->reportDataEndTimeMs = strtotime($extraction['endDate']) * 1000;
        $extraction['json_request']->metadata->title = "{$extraction['extraction_name']}___{$extraction['current']['partnerId']}___";
        $extraction['json_request'] = json_encode($extraction['json_request']);
        $this->helpers->gae_log(LOG_DEBUG, "check json_request" . $extraction['json_request']);

        return $extraction;
    }
}

