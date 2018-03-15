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
        syslog(LOG_DEBUG, "info_sent: "  . json_encode($extraction));

        $curl_response = $this->make_facebook_request('GET', $extraction['current']['accountId'], $extraction['metrics'], $extraction['breakdowns'], $extraction['attribution_window'], $extraction['startDate'], $extraction['endDate'], $extraction['global']['facebook']['long_token']);

        if ($curl_response) {
            return array($this->json_to_csv($curl_response, $extraction['report_header'],$extraction['actions'],$extraction['actions_cost']), '');

        } else {
            $curl_response = $this->make_facebook_request('POST', $extraction['current']['accountId'], $extraction['metrics'], $extraction['breakdowns'], $extraction['attribution_window'], $extraction['startDate'], $extraction['endDate'], $extraction['global']['facebook']['long_token']);
            return array('', $curl_response);
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
            'limit=' . '5000' . '&' .
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

        syslog(LOG_DEBUG, "curl_response: "  . json_encode($curl_response));

        //Checking if there is an error
        if ($method === 'GET') {
            if (strpos($curl_response, '"code":500') !== false) {
                return false;
            } else {
                return $curl_response;
            }
        } else {
            return json_decode(json_decode($curl_response)[0]->body)->report_run_id;
        }
    }

    //Facebook async API call (Set Request)
    function set_async_facebook_request($extraction, $id)
    {

        $helpers = new helpers();
        $access_token = $extraction['global']['facebook']['long_token'];
        $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '?access_token=' . $access_token;
        $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);

        if (json_decode($curl_response)->async_percent_completion === 100) {
            $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '/insights?limit=5000&access_token=' . $access_token;
            $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);

            if (strpos($curl_response, 'Please reduce the amount of data you\'re asking for, then retry your request') !== false) {
                while (true) {
                    return $this->make_async_facebook_request($extraction, $id);
                }
            } else {
                return $this->json_to_csv($curl_response, $extraction['report_header'], $extraction['actions'], $extraction['actions_cost']);
            }

        } else {

            while (true) {
                $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '?access_token=' . $access_token;
                $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);

                if (json_decode($curl_response)->async_percent_completion === 100) {
                    $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '/insights?limit=5000&access_token=' . $access_token;
                    $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);

                    if (strpos($curl_response, 'Please reduce the amount of data you\'re asking for, then retry your request') !== false) {
                        return $this->make_async_facebook_request($extraction, $id);
                    } else {
                        return $this->json_to_csv($curl_response, $extraction['report_header'], $extraction['actions'], $extraction['actions_cost']);
                    }
                }
            }
        }
    }

    //Facebook async API call (Make Request)
    function make_async_facebook_request($extraction, $id)
    {

        $helpers = new helpers();
        $array_pagination = [];
        $array_pagination2 = [];

        $access_token = $extraction['global']['facebook']['long_token'];
        $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '/insights?limit=1000&access_token=' . $access_token;
        $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);
        $array_pagination = array_merge($array_pagination, json_decode($curl_response)->data);

        if (json_decode($curl_response)->paging->next) {

            while (true) {

                $endpoint = 'https://graph.facebook.com/v2.10/' . $id . '/insights?limit=1000&access_token=' . $access_token . '&after=' . json_decode($curl_response)->paging->cursors->after;
                $curl_response = $helpers->set_curl('', $endpoint, '', 'GET', null);
                $array_pagination = array_merge($array_pagination, json_decode($curl_response)->data);

                if (!json_decode($curl_response)->paging->next) {
                    array_push($array_pagination2, $array_pagination);
                    return $this->json_to_csv(json_encode($array_pagination2), $extraction['report_header'], $extraction['actions'], $extraction['actions_cost']);

                } else {

                }
            }

        } else {
            array_push($array_pagination2, $array_pagination);
            return $this->json_to_csv(json_encode($array_pagination2), $extraction['report_header'], $extraction['actions'], $extraction['actions_cost']);
        }
    }

    //Facebook API call (JSON to CSV)
    function json_to_csv($curl_response, $header, $actions, $actions_cost)
    {
        $out = '';
        $sum = 0;
        $helpers = new helpers();

        //JSON to CSV string
        foreach (json_decode($curl_response) as $key => $response) {

            $array_of_content = is_array($response) ? $response : json_decode($response->body)->data;

            foreach ($array_of_content as $line) {
                $i = 0;
                foreach ($line as $key => $value) {

                    if ($key === explode(",", $header)[$i]) {

                        if (!is_array($value)) {
                            $value = str_replace(",", "__|__", $value);
                            $value = str_replace(array("\r", "\n"), '', $value);

                        } else {

                            if ($key === 'actions') {
                                $actions_array = explode(",", $actions);
                                $i = $i + count($actions_array);
                                foreach ($actions_array as $action) {
                                    $found = false;
                                    foreach ($value as $subline) {
                                        if (isset($subline->action_type) && isset($subline->value) ){
                                            if ($action === $subline->action_type) {
                                                $actionsArray[] = $subline->value;
                                                $found = true;
                                                break;
                                            }
                                        }

                                    }
                                    if (!$found){
                                        $actionsArray[] = '0';
                                    }
                                }
                            }

                            if ($key === 'cost_per_action_type') {
                                $actions_cost_array = explode(",", $actions_cost);
                                $i = $i + count($actions_cost_array);
                                foreach ($actions_cost_array as $action) {
                                    $found = false;
                                    foreach ($value as $subline) {
                                        if (isset($subline->action_type) && isset($subline->value) ){
                                            if ($action === $subline->action_type) {
                                                $actionsCostArray[] = $subline->value;
                                                $found = true;
                                                break;
                                            }
                                        }

                                    }
                                    if (!$found){
                                        $actionsCostArray[] = '0';
                                    }
                                }
                            }


                            //Summig up all values
                            foreach ($value as $subline) {
                                if (isset($subline->value)){
                                    $sum = $sum + floatval($subline->value);
                                }
                            }
                            $value = (string)$sum;
                            $sum = 0;
                        }

                        $outputArray[] = $value;

                        if (isset($actionsArray)){
                            foreach ($actionsArray as $action_value) {
                                //
                                //$log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], 'async', $action_value);
                                //$helpers->live_log($extraction, $log_values);
                                //
                                $outputArray[] = $action_value;
                            }
                            $actionsArray = [];
                        }

                        if (isset($actionsCostArray)){
                            foreach ($actionsCostArray as $action_value) {
                                $outputArray[] = $action_value;
                            }
                            $actionsCostArray = [];
                        }

                    } else {

                        for ($x = $i; $x <= count(explode(",", $header)) - 1; $x++) {

                            if ($key === explode(",", $header)[$x]) {

                                if (!is_array($value)) {
                                    $value = str_replace(",", "__|__", $value);
                                    $value = str_replace(array("\r", "\n"), '', $value);

                                } else {

                                    if ($key === 'actions') {
                                        $actions_array = explode(",", $actions);
                                        $i = $i + count($actions_array);
                                        foreach ($actions_array as $action) {
                                            $found = false;
                                            foreach ($value as $subline) {
                                                if ($action === $subline->action_type) {
                                                    $actionsArray[] = $subline->value;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found){
                                                $actionsArray[] = '0';
                                            }
                                        }
                                    }

                                    if ($key === 'cost_per_action_type') {
                                        $actions_cost_array = explode(",", $actions_cost);
                                        $i = $i + count($actions_cost_array);
                                        foreach ($actions_cost_array as $action) {
                                            $found = false;
                                            foreach ($value as $subline) {
                                                if ($action === $subline->action_type) {
                                                    $actionsCostArray[] = $subline->value;
                                                    $found = true;
                                                    break;
                                                }
                                            }
                                            if (!$found){
                                                $actionsCostArray[] = '0';
                                            }
                                        }
                                    }


                                    //Summig up all values
                                    foreach ($value as $subline) {
                                        $sum = $sum + floatval($subline->value);
                                    }
                                    $value = (string)$sum;
                                    $sum = 0;
                                }
                                $outputArray[] = $value;

                                if (isset($actionsArray)){
                                    foreach ($actionsArray as $action_value) {
                                        $outputArray[] = $action_value;
                                    }
                                    $actionsArray = [];
                                }

                                if (isset($actionsCostArray)){
                                    foreach ($actionsCostArray as $action_value) {
                                        $outputArray[] = $action_value;
                                    }
                                    $actionsCostArray = [];
                                }

                                $i = $x;
                                break;
                            } else {
                                $outputArray[] = '0';
                            }
                        }
                    }
                    $i++;
                }
                $out .= implode(",", $outputArray) . "\r\n";
                $outputArray = [];
            }
        }

        //Return API data
        return $out;

    }

}