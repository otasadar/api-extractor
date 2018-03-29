<?php

/**
 * Yandex API extraction
 */

include_once  __DIR__ . '/helpers.php';


class yandex
{
    private $helpers;
    public function __construct()
    {
        $this->helpers = new helpers();
    }

    public function set_yandex_request($extraction) {

        $headers = array('Authorization: Bearer '.$extraction['current']['accessToken'],
            'Client-Login: '.$extraction['current']['clientLogin'],
            'Returnmoneyinmicros: false',
            'processingMode: offline',
            'Skipreportheader: true',
            'SkipColumnHeader: true',
            'Skipreportsummary: true');
        $version= $extraction['global']['yandex']['api_version'];
        $endpoint = "https://api.direct.yandex.com/json/$version/reports";

        $curl_response = $this->helpers->set_curl_raw($headers, $endpoint, $extraction['json_request'], 'POST', null);
        $this->helpers->gae_log(LOG_DEBUG, 'yandex header:'.json_encode($curl_response['header']));

        if (isset($curl_response['header']['retryIn']))  {
            sleep($curl_response['header']['retryIn']);
            $log_values = Array( $extraction['current']['clientLogin'], "WAITING", $curl_response['header']['retryIn'] );
            $extraction = $this->helpers->live_log($extraction, $log_values);
            return $this->set_yandex_request($extraction);
        } else {
            $extraction['csv_output'] = $this->tsv_to_csv($curl_response['body']);
            $this->helpers->gae_log(LOG_DEBUG, "yandex body:".mb_strlen($extraction['csv_output']));
            return $extraction;
        }

    }

    public function tsv_to_csv($csv_output) {

        $csv_output = explode("\n", $csv_output);

        $tmp_csv_output = '';
        foreach ($csv_output  as $line) {
            $tmp_csv_output .= str_replace("\t", ",", $line)."\n";
        }

        return $tmp_csv_output;

    }
}