<?php

/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 2/17/2018
 * Time: 12:55 PM
 */

include_once __DIR__ . '/helpers.php';


class facebook
{

    //Facebook API call (Set Request)
    function set_facebook_request($extraction)
    {
        $curl_response = $this->make_facebook_request('POST', $extraction['current']['accountId'], $extraction['metrics'], $extraction['breakdowns'], $extraction['attribution_window'], $extraction['startDate'], $extraction['endDate'], $extraction['global']['facebook']['long_token']);

        if ($curl_response !== 'error') {
            return $curl_response;

        } else {
            return 'error';
        }
    }

    //Facebook API call (Make Request)
    function make_facebook_request($method, $account, $metrics, $breakdowns, $attribution_window, $startDate, $endDate, $access_token)
    {

        //Facebook query
        $query = new StdClass();
        $payload_builder = [];
        $helpers = new helpers();

        //DURING clausula
        $during = "time_range={'since':'" . $startDate . "','until':'" . $endDate . "'}";

        //GET URL query constructor
        $query->method = $method;
        $query->relative_url = $account . '/insights?' .
            $during . '&' .
            'level=' . 'ad' . '&' .
            'include_headers=' . 'false' . '&' .
            'limit=' . '1000' . '&' .
            'time_increment=' . '1' . '&' .
            'fields=' . $metrics . '&' .
            "action_attribution_windows=['" . $attribution_window . "']" . '&' .
            "breakdowns=['" . $breakdowns . "']";

        //Array push
        array_push($payload_builder, $query);
        $query = new StdClass();

        //Call headers
        $headers = array('contentType: application/json; charset=UTF-8');

        //End point
        $endpoint = 'https://graph.facebook.com/v2.10' . '?access_token=' . $access_token;

        //Payload data
        $payload = array('batch' => str_replace('\\', '', json_encode($payload_builder)), 'format' => 'json', 'method' => 'post');

        //CURL request
        $curl_response = $helpers->set_curl($headers, $endpoint, $payload, 'POST', null);

        //Checking if there is an error
        if (json_decode(json_decode($curl_response)[0]->code) === 200){
            return json_decode(json_decode($curl_response)[0]->body)->report_run_id;
        } else {
            return 'error';
        }


    }

    //Facebook async API call (Set Request)
    function set_async_facebook_request($extraction, $id)
    {
        $helpers = new helpers();
        $access_token = $extraction['global']['facebook']['long_token'];
        $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '?access_token=' . $access_token;
        $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);
        $date1 = date('Y-m-d H:i:s', json_decode($curl_response)->time_ref);
        $date2 = date('Y-m-d H:i:s');
        $time_diff_since_created = $helpers->date_difference($date1, $date2, 'H');

        if (json_decode($curl_response)->async_percent_completion === 100) {
            $csv = file_get_contents('https://www.facebook.com/ads/ads_insights/export_report?report_run_id=' . $id . '&name=myreport&format=csv&access_token=' . $extraction['global']['facebook']['long_token']);
            return array('done', $csv);

        } else {

            if (json_decode($curl_response)->async_status === 'Job Failed' || $time_diff_since_created > 0) {
                $extraction['startDate'] = json_decode($curl_response)->date_start;
                $extraction['endDate'] = json_decode($curl_response)->date_stop;
                $report_request_id = $this->set_facebook_request($extraction);
                return array('fail', $report_request_id);

            } else {

                return array('loading', json_decode($curl_response)->async_percent_completion);
            }

        }
    }
}