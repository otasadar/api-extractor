<?php

/**
 * DCM Functions
 *  - Validations functions
 *  - Group of request for obtain one report data
 *  - Helps specific for DCM, could be rearrenge for future API's as helpers
 */

include_once  __DIR__ . '/helpers.php';


class dcm
{

    /**
     * DCM Validations for avoid run report on wrong or non-existing ID's
     */
    private $helpers;
    public function __construct()
    {
        $this->helpers = new helpers();
    }

    // DCM Get all profiles ID
    function get_profilesIds($extraction)
    {

        $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles";

        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);
        $curl_response = json_decode($curl_response, true);

        $profileIdsValidated = [];
        foreach ($curl_response['items'] as $key => $result) {
            $profileIdsValidated [] = $result['profileId'];
        }
        if (!empty($profileIdsValidated)) {
            $this->helpers->gae_log(LOG_DEBUG, "dcm_check_profilesIds " . implode(',', $profileIdsValidated));
        }
        sleep(1);
        return $profileIdsValidated;

    }

    // DCM Check Floodlight IDs list is valid
    function check_floodlightConfigIds($profileId, $extraction)
    {

        $floodlightConfigIds = [];
        foreach ($extraction['accountsData'][$profileId] as $row) {
            $floodlightConfigIds[] = $row['floodlightConfigId'];
        }
        $floodlightConfigIds = "?ids=" . implode($floodlightConfigIds, '&ids=');
        $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles/$profileId/floodlightConfigurations$floodlightConfigIds";

        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);
        $curl_response = json_decode($curl_response, true);

        $floodlightConfigIdsValidated = [];
        foreach ($curl_response['floodlightConfigurations'] as $key => $result) {
            $floodlightConfigIdsValidated [] = $result['id'];
        }
        if (!empty($floodlightConfigIdsValidated)) {
            $this->helpers->gae_log(LOG_DEBUG, "dcm_check_floodlightConfigIds " . implode(',', $floodlightConfigIdsValidated));
        }
        sleep(1);
        return $floodlightConfigIdsValidated;

    }

    // DCM Check advertiserIds list is valid
    function check_advertiserIds($profileId, $extraction)
    {


        $advertiserIds = [];
        $this->helpers->gae_log(LOG_DEBUG, "check control adid-1" . json_encode($advertiserIds));
        $this->helpers->gae_log(LOG_DEBUG, "check control adid" . json_encode($extraction['accountsData'][$profileId] ));

        foreach ($extraction['accountsData'][$profileId] as $row) {
            $advertiserIds[] = $row['advertiserId'];
        }
        $advertiserIds = "?ids=" . implode($advertiserIds, '&ids=');
        $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles/$profileId/advertisers$advertiserIds";

        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);
        $curl_response = json_decode($curl_response, true);

        $advertiserIdsValidated = [];
        foreach ($curl_response['advertisers'] as $key => $result) {
            $advertiserIdsValidated [] = $result['id'];
        }
        if (!empty($advertiserIdsValidated)) {
            $this->helpers->gae_log(LOG_DEBUG, "dcm_check_advertiserIds " . implode(',', $advertiserIdsValidated));
        }
        sleep(1);
        return $advertiserIdsValidated;

    }


    /**
     * DCM API Report Requests:
     */

    // DCM First Function for start a combo of DCM request
    function start($extraction, $profileId)
    {
        // Define report
        $api_response = $this->report_setup($profileId, $extraction);
        $extraction = $this->helpers->check_n_control($api_response, $extraction);
        if (isset($extraction['current']['error'])) return $extraction;

        // Run report
        $extraction['current']['reportId'] = $api_response->id;
        $api_response = $this->run_report($api_response, $extraction);
        $extraction = $this->helpers->check_n_control($api_response, $extraction);
        if (isset($extraction['current']['error'])) return $extraction;

        $extraction['current']['fileId'] = $api_response->id;
        $extraction['reportsData'][] = $extraction['current'];

        return $extraction;

    }

    // DCM request 1 - Setup report and get report id
    function report_setup($profileId, $extraction)
    {
        // First request to get DCM Report ID
        $headers = array('Content-type: application/json');
        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles/$profileId/reports?access_token=" . $extraction['access_token'];
        $this->helpers->gae_log(LOG_DEBUG, "endpoint " . $endpoint);
        $this->helpers->gae_log(LOG_DEBUG, "json_request " . $extraction['json_request']);


        $curl_response = $this->helpers->set_curl($headers, $endpoint, $extraction['json_request'], 'POST', null);
        $curl_response = json_decode($curl_response);

        //$this->helpers->gae_log(LOG_DEBUG, "profileId ".$profileId );
        //
        $this->helpers->gae_log(LOG_DEBUG, "dcm_report_setup " . json_encode($curl_response));
        sleep(1);
        return $curl_response;
    }

    // DCM request 2 - Run report for get URL
    function run_report($api_response, $extraction)
    {

        $reportId = $api_response->id;
        $profileId = $api_response->ownerProfileId;
        $access_token = $extraction['access_token'];
        $headers = array("Authorization: Bearer $access_token", 'Accept: application/json');



        // Second request to get DCM report CSV status & final URL
        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles/$profileId/reports/$reportId/run";
        $curl_response = $this->helpers->set_curl($headers, $endpoint, '', 'POST', null);
        $curl_response = json_decode($curl_response);

        //$this->helpers->gae_log(LOG_DEBUG, "reportId ".$reportId );
        //$this->helpers->gae_log(LOG_DEBUG, "profileId ".$profileId );
        $this->helpers->gae_log(LOG_DEBUG, "dcm_run_report " . json_encode($curl_response));
        sleep(1);
        return $curl_response;
    }

    // DCM request 3 - Check if media report file is generated
    function get_report_url($extraction)
    {


        $profileId = $extraction['current']['profileId'];
        //$reportId = $api_response->reportId;
        //$fileId = $api_response->id;
        $reportId = $extraction['current']['reportId'];
        $fileId = $extraction['current']['fileId'];
        $access_token = $extraction['access_token'];
        $headers = array("Authorization: Bearer $access_token ", 'Accept: application/json');


        // Second request to get DCM report CSV status & final URL

        $api_version = $extraction['global']['dcm']['api_version'];
        $endpoint = "https://www.googleapis.com/dfareporting/$api_version/userprofiles/$profileId/reports/$reportId/files/$fileId";
        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);
        $curl_response = json_decode($curl_response);

        //$this->helpers->gae_log(LOG_DEBUG, "reportId ".$reportId );
        //$this->helpers->gae_log(LOG_DEBUG, "profileId ".$profileId );
        //$this->helpers->gae_log(LOG_DEBUG, "fileId ".$fileId );
        $this->helpers->gae_log(LOG_DEBUG, "dcm_get_report_url " . json_encode($curl_response));
        sleep(1);
        return $curl_response;
    }

    // DCM request 4 - Get CSV content by URL
    function get_report_url_content($api_response, $extraction)
    {

        $log_values = Array("original CSV : {$extraction['file_name']} " . $api_response->urls->browserUrl);
        $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));

        $url = $api_response->urls->apiUrl;
        $status = $api_response->status;
        $access_token = $extraction['access_token'];

        $this->helpers->gae_log(LOG_DEBUG, "url " . $url);
        $this->helpers->gae_log(LOG_DEBUG, "status " . $status);

        $headers = array("Authorization: Bearer $access_token");
        $endpoint = "$url";
        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET');
        $this->helpers->gae_log(LOG_DEBUG, "dcm_get_report_url_content: " . strlen($curl_response));

        return $curl_response; // check if is a CSV data or JSON
    }

    // DCM recursive functions for get report data
    function ask_until_status_available($extraction)
    {

        $extraction = $this->helpers->check_access_token($extraction);
        $api_response = $this->get_report_url($extraction);

        if ($api_response->status === "REPORT_AVAILABLE") {
            $api_response2 = $this->get_report_url_content($api_response, $extraction);
            return $api_response2;

        } else if ($api_response->status === "PROCESSING") {
            $this->helpers->gae_log(LOG_DEBUG, "queueDelay:60");
            sleep(60);
            return $this->ask_until_status_available($extraction);


            /*
            if ($extraction['queueDelay'] < $extraction['max_execution_sec']) {

                $this->helpers->gae_log(LOG_DEBUG, "queueDelay" . $extraction['queueDelay']);
                sleep($extraction['queueDelay']);
                return ask_until_status_available($api_response, $extraction);

            } else {
                status_log("DCM {$extraction['report_type']} TIMEOUT" .
                    return_isset($extraction['current']['profileId'], "profileId: {$extraction['current']['profileId']}") .
                    return_isset($extraction['current']['floodlightConfigId'], "floodlightConfigId: {$extraction['current']['floodlightConfigId']}") .
                    return_isset($extraction['current']['advertiserId'], "advertiserId: {$extraction['current']['advertiserId']}"));
                $this->helpers->gae_log(LOG_DEBUG, "TIMEOUT " . $extraction['max_execution_sec']);
            }
            */

        } else {
            return $api_response->status;
        }
    }


    /**
     * DCM Helpers:
     */

    // Merge Duplicate's Ids in a single array with all sub id and other data
    function merge_profileId_array($original_array)
    {

        /*
        $arr1 = array(
            array('profileId' => '2896506', 'advertiserId' => '6197584'),
            array('profileId' => '2896506', 'advertiserId' => '6203268'),
            array('profileId' => '2896506', 'advertiserId' => '6203788'),
            array('profileId' => '2896506', 'advertiserId' => '6203270'),
            array('profileId' => '2896506', 'advertiserId' => '6199969'),
            array('profileId' => '2719739', 'advertiserId' => '5912534'),

            array('profileId' => '2719829', 'advertiserId' => '8271328'),
            array('profileId' => '2719829', 'advertiserId' => '5452626'),
            array('profileId' => '2719829', 'advertiserId' => '5449796'),
            array('profileId' => '2719829', 'advertiserId' => '5454647'),



        );
        */

        $outer_array = array();
        $unique_array = array();


        foreach ($original_array as $key => $value) {
            $inner_array = array();

            $profileId_value = $value['profileId'];
            if (!in_array($value['profileId'], $unique_array)) {
                array_push($unique_array, $profileId_value);

                unset($value['profileId']);
                array_push($inner_array, $value);
                $outer_array[$profileId_value] = $inner_array;


            } else {
                unset($value['profileId']);
                array_push($outer_array[$profileId_value], $value);

            }
        }

        return $outer_array;

    }


    // GET line from dynamic header
    function get_report_header($raw_data, $extraction, $needle)
    {

        if (empty($extraction['csv_output'])) {
            $rows = explode("\n", $raw_data);

            if (isset($needle)) {
                for ($i = 0; $i < count($rows); $i++) {

                    if (strpos($rows[$i], $needle) !== false) {

                        switch ($extraction['report_type']) {
                            case "STANDARD":
                                $extraction['csv_output'] = "profileId," . $rows[$i] . "\n";
                                $this->helpers->gae_log(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");

                                break;
                            case "FLOODLIGHT":
                                $extraction['csv_output'] = "profileId," . $rows[$i] . "\n";
                                $this->helpers->gae_log(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");
                                break;

                            case "CROSS_DIMENSION_REACH":
                                $extraction['csv_output'] = "AdvertiserId," . $rows[$i] . "\n";
                                $this->helpers->gae_log(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");
                                break;
                        }


                        break;
                    }
                }
            }
        }

        return $extraction;

    }

    // Clean dynamic header
    function headers_cleaner($raw_data, $extraction, $needle, $remove_last_line)
    {
        if (empty($raw_data)) {
            return $raw_data;
        } else {
            $rows = explode("\n", $raw_data);

            $total_rows = count($rows);

            //remove headers
            if (isset($needle)) {
                for ($i = 0; $i < $total_rows; $i++) {

                    if (strpos($rows[$i], $needle) !== false) {
                        $this->helpers->gae_log(LOG_DEBUG, "founded needle:" . $needle);
                        $this->helpers->gae_log(LOG_DEBUG, "removed last line" . $rows[$i]);


                        if (empty(trim($rows[($i + 1)]))) {
                            $this->helpers->gae_log(LOG_DEBUG, "removed space line" . $rows[($i + 1)]);
                            unset($rows[($i + 1)]); //next line is empty
                        }
                        unset($rows[$i]);
                        break;
                    } else {
                        $this->helpers->gae_log(LOG_DEBUG, "removed line" . $rows[$i]);
                        unset($rows[$i]);
                    }

                }
            } else {
                $this->helpers->gae_log(LOG_DEBUG, "needle not FOUND:" . $raw_data);
            }

            // remove footer
            if (isset($remove_last_line)) {
                $this->helpers->gae_log(LOG_DEBUG, "removed footer line" . end($rows));
                array_pop($rows);
                $this->helpers->gae_log(LOG_DEBUG, "removed footer line2" . end($rows));
                array_pop($rows);
            }

            //add id to beginning
            foreach ($rows as $key => $line) {
                switch ($extraction['report_type']) {
                    case "STANDARD":
                    case "FLOODLIGHT":
                        $rows[$key] = $extraction['current']['profileId'] . "," . $line;
                        break;

                    case "CROSS_DIMENSION_REACH":
                        $rows[$key] = $extraction['current']['advertiserId'] . "," . $line;
                        break;
                }
            }

            $filter_data = implode("\n", $rows);
            return $filter_data . "\n";
        }

    }

    // Combine results of queries
    function preparing_csv_file($raw_data, $extraction)
    {

        if (mb_strlen($raw_data) > 1) {

            //$this->helpers->storage_insert_combine_delete($extraction);
            $extraction['csv_output'] .= $raw_data;

            $log_values = Array(
                $extraction['current']['profileId'],
                $this->helpers->return_isset($extraction['current']['advertiserId']),
                $this->helpers->return_isset($extraction['current']['floodlightConfigId']),
                $this->helpers->return_isset($extraction['current']['advertiserName']),
                $this->helpers->return_isset($extraction['current']['networkName']),
                "OK",
                mb_strlen($raw_data));
            $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $this->helpers->live_log($extraction, $log_values);

        } else {

            $log_values = Array(
                $extraction['current']['profileId'],
                $this->helpers->return_isset($extraction['current']['advertiserId']),
                $this->helpers->return_isset($extraction['current']['floodlightConfigId']),
                $this->helpers->return_isset($extraction['current']['advertiserName']),
                $this->helpers->return_isset($extraction['current']['networkName']),
                "EMPTY",
                mb_strlen($raw_data));
            $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $this->helpers->gae_log(LOG_DEBUG, $raw_data);
            $extraction = $this->helpers->live_log($extraction, $log_values);
        }
        return $extraction;
    }
}

