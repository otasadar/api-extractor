<?php

/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 2/17/2018
 * Time: 12:55 PM
 */

include_once __DIR__ . '/helpers.php';


class ga
{

    //Google Analytics API call (HTTP API request)
    function set_ga_request($extraction, $paginate)
    {
        $helpers = new helpers();

        //Call headers
        $headers = array('Authorization : Bearer ' . $extraction['access_token'], 'Content-Type: application/json');

        //URL
        $endpoint = 'https://content-analyticsreporting.googleapis.com/v4/reports:batchGet?alt=json';

        //Reports
        $report_request = array(
            "viewId" => $extraction['current']['accountId'],
            "dateRanges" => $extraction['dateRanges'],
            "metrics" => $extraction['metrics'],
            "dimensions" => $extraction['dimensions'],
            "pageSize" => $extraction['pageSize']
        );

        if ($paginate) {
            $report_request['pageToken'] = $paginate;
        }

        //Payload data
        $payload = array(
            "reportRequests" => $report_request
        );

        $payload = json_encode(json_decode(json_encode($payload)));


        //CURL request
        $curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

        //Return API data
        if ($curl_response) {
            if ($paginate) {
                return $curl_response;
            } else {
                return $this->json_to_csv($curl_response, $extraction);
            }
        } else {
            return false;
        }
    }

    //JSON to CSV
    function json_to_csv($curl_response, $extraction)
    {
        $reports_array = '';
        $reports_array = json_decode($curl_response, true)['reports'];
        $pagination_array = array();

        foreach ($reports_array as $report) {

            //JSON to CSV first page
            $csv = $this->json_to_csv_paginate($report);

            //JSON to CSV next pages
            while (true) {
                if ($report['nextPageToken']) {
                    $report_paginate = $this->set_ga_request($extraction, $report['nextPageToken']);
                    $report = json_decode($report_paginate, true)['reports'][0];
                    $csv = $csv . $this->json_to_csv_paginate($report);

                } else {
                    return $csv;
                }
            }
        }
    }

    //JSON to CSV paginate
    function json_to_csv_paginate($report)
    {

        $csv = '';
        foreach ($report['data']['rows'] as $row) {

            $csv .= implode(", ", $row['dimensions']) . ',' . implode(", ", $row['metrics'][0]['values']) . ',' . "\r\n";
        }

        $csv = rtrim(trim($csv), ',');

        return $csv;

    }

}