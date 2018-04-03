<?php

/**
 * DS Functions
 */

include_once  __DIR__ . '/helpers.php';


class ds
{


    private $helpers;
    public function __construct()
    {
        $this->helpers = new helpers();
    }


    /**
     * DS API Report Requests:
     */

    // DS First Function for start a combo of DS request
    function start($extraction)
    {
        $api_response = $this->report_request($extraction);
        $extraction['current']['reportId'] = $api_response->id;
        $extraction = $this->helpers->check_n_control($api_response, $extraction);
        if (isset($extraction['current']['error'])) return $extraction;

        $extraction['reportsData'][] = $extraction['current'];
        return $extraction;

    }

    // DS request 1 - Setup report and get report query id
    function report_request($extraction)
    {

        $headers = array("Authorization: Bearer {$extraction['access_token']}" , 'Content-Type: application/json' );
        $api_version = $extraction['global']['ds']['api_version'];
        $endpoint = "https://www.googleapis.com/doubleclicksearch/$api_version/reports";

        $curl_response = $this->helpers->set_curl($headers, $endpoint, $extraction['json_request'], 'POST', null);
        $curl_response = json_decode($curl_response);

        $this->helpers->gae_log(LOG_DEBUG, "ds_report_request " . json_encode($curl_response));
        sleep(1);
        return $curl_response;
    }

    // DS request 2 - Run report for get URL
    function report_get($extraction)
    {

        $reportId= $extraction['current']['reportId'];
        $api_version = $extraction['global']['ds']['api_version'];
        $endpoint = "https://www.googleapis.com/doubleclicksearch/$api_version/reports/$reportId";

        $headers = array("Authorization: Bearer {$extraction['access_token']}" , 'Content-Type: application/json' );
        $curl_response = $this->helpers->set_curl($headers, $endpoint, null, 'GET', null);
        $curl_response = json_decode($curl_response);

        $this->helpers->gae_log(LOG_DEBUG, "ds_report_get ");
        sleep(1);
        return $curl_response;
    }


    // DS request 3 - Get data using URL
    function report_data_from_url($api_response, $extraction)
    {

        $report_url = $api_response->files[0]->url;
        $headers = array("Authorization: Bearer {$extraction['access_token']}");

        $curl_response = $this->helpers->set_curl($headers, $report_url, null, 'GET');

        // todo check size, for avoid memory leaks
        return $curl_response;
    }





    // DS recursive functions for get report data
    function ask_until_status_available($extraction)
    {
        $extraction = $this->helpers->check_access_token($extraction);
        $api_response = $this->report_get($extraction);
        $status = $api_response->isReportReady;

        $log_values = Array($extraction['current']['agencyId'],$extraction['current']['agencyName'],"isReportReady", $status);
        $extraction = $this->helpers->live_log($extraction,$log_values);


        if ($status=== true) {
            return $api_response;

        }  else {
            $this->helpers->gae_log(LOG_DEBUG, "queueDelay: 60s");
            sleep(60);
            return $this->ask_until_status_available($extraction);
        }
    }





}

