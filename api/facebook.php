<?php


include_once __DIR__ . '/helpers.php';


class facebook
{

    private $helpers;
    public function __construct()
    {
        $this->helpers = new helpers();
    }

    //Facebook API call (Report ID Request)
    function facebook_report_id_request($extraction)
    {

        $account = $extraction['current']['accountId'];
        $metrics = $extraction['metrics'];
        $breakdowns = $extraction['breakdowns'];
        $attribution_window = $extraction['attribution_window'];
        $startDate = $extraction['startDate'];
        $endDate = $extraction['endDate'];
        $access_token = $extraction['global']['facebook']['long_token'];
        $version = $extraction['global']['facebook']['api_version'];


        //DURING clausula
        $during = "time_range={'since':'" . $startDate . "','until':'" . $endDate . "'}";

        //GET URL query constructor
        $batch = array('method'=>'POST',
            'relative_url'=> $account . '/insights?' .
                $during . '&' .
                'level=' . 'ad' . '&' .
                'include_headers=' . 'false' . '&' .
                'limit=' . '1000' . '&' .
                'time_increment=' . '1' . '&' .
                'fields=' . $metrics . '&' .
                "action_attribution_windows=['" . $attribution_window . "']" . '&' .
                "breakdowns=['" . $breakdowns . "']");
        $payload['batch']=  "[".str_replace('\\', '', json_encode($batch))."]";
        $payload['format']= 'json';
        $payload['method']= 'post';

        $headers = array('contentType: application/json; charset=UTF-8');
        $endpoint = "https://graph.facebook.com/$version?access_token=$access_token";
        $curl_response = $this->helpers->set_curl($headers, $endpoint, $payload, 'POST');

        if (json_decode(json_decode($curl_response)[0]->code) === 200){
            return json_decode(json_decode($curl_response)[0]->body)->report_run_id;
        } else {
            $this->helpers->gae_log(LOG_DEBUG, 'Error FB Curl response : '.json_encode($curl_response, JSON_UNESCAPED_UNICODE));
            return 'error';
        }

    }

    //Facebook async API call (Set Request)
    function set_async_facebook_request($extraction, $id)
    {
        $version = $extraction['global']['facebook']['api_version'];
        $access_token = $extraction['global']['facebook']['long_token'];
        $endpoint = "https://graph.facebook.com/$version/$id?access_token=$access_token";
        $curl_response = $this->helpers->set_curl('', $endpoint, '', 'GET', null);
        $date1 = date('Y-m-d H:i:s', json_decode($curl_response)->time_ref);
        $date2 = date('Y-m-d H:i:s');
        $time_diff_since_created = $this->helpers->date_difference($date1, $date2, 'H');

        if (json_decode($curl_response)->async_percent_completion === 100) {

            $csv = file_get_contents('https://www.facebook.com/ads/ads_insights/export_report?report_run_id=' . $id . '&name=myreport&format=csv&access_token=' . $extraction['global']['facebook']['long_token']);
            return array('done', $csv);

        } else {

            if (json_decode($curl_response)->async_status === 'Job Failed' || $time_diff_since_created > 0) {
                $extraction['startDate'] = json_decode($curl_response)->date_start;
                $extraction['endDate'] = json_decode($curl_response)->date_stop;
                $report_request_id = $this->facebook_report_id_request($extraction);
                return array('fail', $report_request_id);

            } else {

                return array('loading', json_decode($curl_response)->async_percent_completion);
            }

        }
    }

    // Recursive function for get results
    function wait_until_status_done ($extraction) {

        $current= $extraction['current'];
        $response = $this->set_async_facebook_request($extraction, $current['report_id']);
        $status = $response[0];
        $response_msg = $response[1];

        if ($status === 'loading') {

            $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'REPORT LOADING-> ' . $response_msg . '%', $current['date_range']);
            $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $this->helpers->live_log($extraction, $log_values);

            sleep(60);
            return $this->wait_until_status_done($extraction);
        }

        else if ($status === 'fail') {

            $extraction['current']['attempt']++;

            if ($extraction['current']['attempt'] < 3 && is_numeric($response[1])) {
                // reset id
                $extraction['current']['report_id'] = $response[1];


                // log
                $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'REPORT RESTART new_id:'.$response[1], $current['date_range']);
                $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $this->helpers->live_log($extraction, $log_values);

                sleep(60);
                return $this->wait_until_status_done($extraction);
            }
            else  if ($extraction['current']['attempt'] >= 3) {

                $extraction['current']['attempt'] = 1;
                // log
                $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'REPORT FAILED two many attempts', $current['date_range']);
                $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $this->helpers->live_log($extraction, $log_values);
                $extraction['current']['data'] = $response_msg;
                return $extraction;

            } else {
                // log
                $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'REPORT FAILED new_id:'.$response[1], $current['date_range']);
                $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
                $extraction = $this->helpers->live_log($extraction, $log_values);
                $extraction['current']['data'] = $response_msg;
                return $extraction;
            }


        }

        else if ($status === 'done') {

            $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'REPORT RECEIVED FROM API', $current['date_range']);
            $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $this->helpers->live_log($extraction, $log_values);
            $extraction['current']['data'] = $response_msg;
            return $extraction;

        }

        else {
            $this->helpers->gae_log(LOG_DEBUG, "test account_data:".json_encode($response));

            $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'], 'STATUS UNKNWON:' . $response_msg , $current['date_range']);
            $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
            $extraction = $this->helpers->live_log($extraction, $log_values);

            return $extraction;
        }

    }

    // Save report partial
    function save_report_for_debugging($extraction){
        $current= $extraction['current'];
        $bucket = $extraction['global']['google_storage']['bucket'];
        $object = "{$extraction['extraction_name_ini']}-{$current['accountName']}-{$current['date_range']}.csv";
        file_put_contents("gs://$bucket/{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['timestamp']}/".$object, $current['data']);
        $this->helpers->gae_log(LOG_DEBUG, 'Saving partial:'.$object);
    }

    // Save report data
    function save_report_data ($extraction) {

        $current= $extraction['current'];

        if (mb_strlen($current['data'] ) > 22) {

            if (!isset($extraction['csv_output']) || empty($extraction['csv_output'])) {

                $header = implode(array_slice(explode("\n", $current['data'] ), 0,1));
                $header = preg_replace('/[^,_A-Za-z0-9\-]/', "", $header);
                $header = 'fb_' . str_replace(',',',fb_',$header);

                $header = explode(",",$header);
                $this->helpers->gae_log(LOG_DEBUG, "header elements : ". count($header));

                $header_string_unique = implode(",", $this->helpers->unique_arr($header));

                $body = implode("\n",array_slice(explode("\n", $current['data'] ), 1));
                $current['data'] = $header_string_unique . "\n" . $body;
                $extraction['csv_output'] = $current['data'];

                $this->helpers->create_csv_file($extraction);
                $result = 'REPORT CREATED - HEADER';

            } else {
                $this->helpers->gae_log(LOG_DEBUG, 'sample csv_output:'.substr($extraction['csv_output'], 0, 100));

                $current['data'] = implode("\n", array_slice(explode("\n", $current['data'] ), 1));
                $extraction['csv_output'] =  $current['data'];
                $this->helpers->storage_insert_combine_delete($extraction);
                $result = 'REPORT UPDATED';

            }

        } else {
            $result = 'REPORT IS EMPTY';
        }

        // result
        $log_values = Array($current['accountId'], $current['accountName'], $current['report_id'],  $result, $current['date_range'], mb_strlen($current['data']), $this->helpers->bytesToMBytes(mb_strlen($current['data'])) );
        $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
        $extraction = $this->helpers->live_log($extraction, $log_values);


        return $extraction;

    }

    // Init first Facebook request
    function init_facebook_report_request ($extraction) {

        $report_request_id = $this->facebook_report_id_request($extraction);
        $extraction['current']['date_range'] = $extraction['startDate'] . '/' . $extraction['endDate'];;

        if ($report_request_id !== 'error') {

            $extraction['current']['report_id'] = $report_request_id;
            $extraction['reportsData'][] = $extraction['current'];
            $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], $report_request_id, 'REPORT REQUESTED', $extraction['current']['date_range']);

        } else {
            $log_values = Array($extraction['current']['accountId'], $extraction['current']['accountName'], $report_request_id, 'REPORT ERROR REQUESTED', $extraction['current']['date_range']);
        }
        $this->helpers->gae_log(LOG_DEBUG, json_encode($log_values));
        $extraction = $this->helpers->live_log($extraction, $log_values);

        return $extraction;
    }
}