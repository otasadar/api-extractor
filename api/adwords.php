<?php

/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 2/17/2018
 * Time: 12:55 PM
 */

include_once  __DIR__ . '/helpers.php';


class adwords
{
    // Google Adwords API call (Response handler)
    function handle_adwords_api_response($api_response, $extraction)
    {

        $helpers = new helpers();

        $error_reason = '';
        $error_reason_xml = $api_response[1];
        $error_reason_array = explode('</type>', $error_reason_xml);

        foreach (array_slice($error_reason_array, 0, sizeof(explode('</type>', $error_reason_xml)) - 1) as $item) {
            $error_reason = $error_reason === '' ? explode('Error.', $item)[1] : $error_reason . ', ' . explode('Error.', $item)[1];
        }


        $log_values = Array(
            $extraction['current']['accountId'],
            $extraction['current']['accountName'],
            $api_response[0],
            $error_reason);
        syslog(LOG_DEBUG, json_encode($log_values));
        $extraction = $helpers->live_log($extraction, $log_values);
        return $extraction;
    }

    // Google Adwords API call (HTTP API request and AND conditions)
    function set_adwords_request($extraction)
    {
        $helpers = new helpers();
        $account =$extraction['current']['accountId'];
        $report = $extraction['report_type'];
        $metrics = $extraction['metrics'];
        $startDate = $extraction['startDate'];
        $endDate = $extraction['endDate'];
        $access_token = $extraction['access_token'];
        $developer_token = $extraction['global']['google']['developer_token'];
        $skip_headers = 'true';
        /*
        if ($extraction['current']['key'] === 0) {
            $skip_headers = 'false';
        } else {
            $skip_headers = 'true';
        }
        */

        //Call headers
        $headers = array('contentType: application/x-www-form-urlencoded',
            'developerToken: ' . $developer_token,
            'Authorization : Bearer ' . $access_token,
            'clientCustomerId:' . $account,
            'skipReportHeader: true',
            'skipColumnHeader: ' . $skip_headers,
            'skipReportSummary: true',
            'includeZeroImpressions: false');

        //URL
        $api_version = $extraction['global']['adwords']['api_version'];
        $endpoint = "https://adwords.google.com/api/adwords/reportdownload/$api_version";


        //Payload data
        //$payload = '__fmt=CSV&__rdquery=' . ' SELECT ' . $metrics . ' FROM ' . $report . ' ' . 'DURING ' . $date;
        $payload = "__fmt=CSV&__rdquery= SELECT $metrics FROM $report DURING $startDate,$endDate";

        //CURL request

        $curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

        // error case
        if (is_array ($curl_response)){
            syslog(LOG_DEBUG, json_encode($curl_response));
            return $this->handle_adwords_api_response($curl_response, $extraction);
        }

        //Return API data
        if ($curl_response) {
            return $curl_response;
        } else {
            return false;
        }
    }

    // Split dates into smaller period dates
    function split_dates($start_date_str, $split_day_period) {

        $now = new DateTime();
        $start_date = new DateTime($start_date_str);
        $since_start = $start_date->diff(new DateTime($now->format('Ymd')));
        $diff = $since_start->days;

        function addDays ($date, $days) {
            $date = new DateTime($date);
            date_modify($date, "+$days day");
            return date_format($date, 'Ymd');
        }

        $data_periods = [];

        for ($i = 0; $i <= $diff; $i+=$split_day_period) {

            $tmp_period = $i+$split_day_period-1;
            if ($tmp_period > $diff) {
                $tmp_period = $diff;
            }
            $startDate = addDays ($start_date_str, $i);
            $endDate = addDays ($start_date_str, $tmp_period);

            $data_periods[] = array( 'startDate'=> $startDate , 'endDate'=> $endDate  );

        }

        return $data_periods;
    }


}