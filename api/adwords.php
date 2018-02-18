<?php

/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 2/17/2018
 * Time: 12:55 PM
 */
class adwords
{
    // Google Adwords API call (Response handler)
    function handle_adwords_api_response($api_response, $extraction)
    {
        if (is_array($api_response)) {

            if ($api_response[0] === 'error') {

                $error_reason = '';
                $error_reason_xml = $api_response[1];
                $error_reason_array = explode('</type>', $error_reason_xml);

                foreach (array_slice($error_reason_array, 0, sizeof(explode('</type>', $error_reason_xml)) - 1) as $item) {
                    $error_reason = $error_reason === '' ? explode('Error.', $item)[1] : $error_reason . ', ' . explode('Error.', $item)[1];
                }

                syslog(LOG_DEBUG, "AdWords API connection Error " . $api_response);
                status_log("AdWords API_Error" . $api_response . " accountId: {$extraction['current_accountId']} file_name: {$extraction['file_name']}");
                return false;
            }

        } else {

            return $api_response;
        }
    }

// Google Adwords API call (HTTP API request and AND conditions)
    function set_adwords_request($account, $report, $metrics, $startDate, $endDate, $access_token, $developer_token, $skip_headers, $extraction)
    {

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
        $endpoint = 'https://adwords.google.com/api/adwords/reportdownload/v201705';


        //Payload data
        //$payload = '__fmt=CSV&__rdquery=' . ' SELECT ' . $metrics . ' FROM ' . $report . ' ' . 'DURING ' . $date;
        $payload = "__fmt=CSV&__rdquery= SELECT $metrics FROM $report DURING $startDate,$endDate";

        //CURL request
        $curl_response = set_curl_adwords($headers, $endpoint, $payload, 'POST', null, $extraction);

        //Return API data
        if ($curl_response) {
            return $curl_response;
        } else {
            return false;
        }
    }




}