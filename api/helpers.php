<?php

/**
 * Common functions across all API's
 */
class helpers
{
//  SET_APIS - INVOKE API FUNCTION TO LOAD THE ACCOUNTS LISTS
    function get_access_token($client_id, $client_secret, $refresh_token)
    {

        $headers = array('Content-type: application/x-www-form-urlencoded');
        $endpoint = 'https://www.googleapis.com/oauth2/v4/token';
        $payload = 'client_id=' . $client_id . '&client_secret=' . $client_secret . '&refresh_token=' . $refresh_token . '&grant_type=refresh_token';
        $access_token = $this->set_curl($headers, $endpoint, $payload, 'POST', null);

        if ($access_token) {
            return json_decode($access_token)->access_token;
        } else {
            syslog(LOG_DEBUG, 'error access token' . $access_token);
            return false;
        }
    }

    function check_access_token($extraction, $case = null)
    {
        if ($case === 'sheets') {
            $access_token_datetime = $extraction['global']['google_sheet']['access_token_datetime'];
        } else {
            $access_token_datetime = $extraction['access_token_datetime'];
        }
        $now = new DateTime();
        $start_date = new DateTime($access_token_datetime);
        $since_start = $start_date->diff(new DateTime($now->format('Y-m-d H:i:s')));

        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;

        if ($minutes > 50) {
            $client_id = $extraction['global']['google']['client_id'];
            $client_secret = $extraction['global']['google']['client_secret'];

            if ($case === 'sheets') {
                $extraction['global']['google_sheet']['access_token'] = $this->get_access_token($client_id, $client_secret, $extraction['global']['google_sheet']['refresh_token']);
                $extraction['global']['google_sheet']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
            } else {
                syslog(LOG_DEBUG, 'access_token');
                $extraction['access_token'] = $this->get_access_token($client_id, $client_secret, $extraction['refresh_token']);
                $extraction['access_token_datetime'] = $now->format('Y-m-d H:i:s');
            }
        }

        return $extraction;

    }

//  SET CURL ADWORDS - HELPER METHOD THAT ISSUES A CURL REQUEST
/*
    function set_curl_adwords($headers, $endpoint, $payload, $type, $extras, $extraction = null)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);

        if ($extras) {
            foreach ($extras as $extra) {
                curl_setopt($curl, $extra[0], $extra[1]);
            }
        }

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);


        if ($response === false || $info['http_code'] != 200) {
            syslog(LOG_DEBUG, "AdWords Curl Error " . $info['http_code']);
            if ($extraction) {
                status_log("AdWords Curl_Error" . $info['http_code'] . " accountId: {$extraction['current_accountId']} file_name: {$extraction['file_name']}");
            }
            return false;
        } else {
            return $this->handle_adwords_api_response($response, $extraction);
        }
    }
*/

//  SET CURL GENERAL - HELPER METHOD THAT ISSUES A CURL REQUEST
    function set_curl($headers, $endpoint, $payload, $type, $extras = null)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);


        if (isset($extras)) {
            foreach ($extras as $extra) {
                curl_setopt($curl, $extra[0], $extra[1]);
            }
        }

        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if (strpos($info['http_code'], '30') !== false) {
            //result_log($extraction, $log_values);

            syslog(LOG_DEBUG, "URL size before downlodad:" . $this->get_curl_remote_file_size($info['redirect_url']));

            $response = $this->set_simple_curl($info['redirect_url']);
            syslog(LOG_DEBUG, "simple curl after downlodad:" . mb_strlen($response));
            return $response;
        } else if ($response === false || $info['http_code'] != 200) {
            syslog(LOG_DEBUG, "error curl :" . json_encode($info));
            syslog(LOG_DEBUG, "error curl :" . $endpoint);
            syslog(LOG_DEBUG, "error curl :" . json_encode($headers));
            syslog(LOG_DEBUG, "error curl :" . $response);
            syslog(LOG_DEBUG, "error curl :" . $type);
        } else {
            return $response;
        }
    }

    function set_simple_curl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            syslog(LOG_DEBUG, "error set_simple_curl" . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    function get_curl_remote_file_size($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

//  SET CURL - CREATE AND UPDATE CSV FILE
    function create_csv_file($extraction)
    {

        $csv_string = $extraction['csv_output'];
        $storage_data = $extraction['global']['storage_data'];
        $bucket = $extraction['global']['storage_data']['bucket'];


        $access_token = $this->get_storage_access_token($extraction);


        if ($access_token) {
            $resumable_session_url = $this->get_google_storage_session_url($extraction, $bucket, $access_token);
            if (!is_array($resumable_session_url)) {

                $report_metadata_latest = $this->upload_report_to_google_storage($resumable_session_url, $csv_string);

                if (!is_array($report_metadata_latest)) {

                    return true;

                } else {

                    return false;
                }

            } else {
                syslog(LOG_DEBUG, 'Report metadata has not been updated to the Data Base.');
            }
        } else {
            syslog(LOG_DEBUG, 'access token not found');
        }
    }

//  SET CURL - GOOGLE CLOUD SESSION URL
    function get_google_storage_session_url($extraction, $bucket, $access_token)
    {
        //$file_name = urlencode (  "{$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");
        $file_name = urlencode("{$extraction['file_name']}");

        $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
        $endpoint = "https://www.googleapis.com/upload/storage/v1/b/$bucket/o?uploadType=resumable&predefinedAcl=publicRead&name=$file_name";
        $extras = array(array(CURLOPT_HEADER, 1));
        $payload = json_encode(['cacheControl' => 'public, max-age=0, no-transform']);

        syslog(LOG_DEBUG, 'cloud storage ' . $endpoint);

        //Cloud session URL
        $resumable_session_url = $this->set_curl($headers, $endpoint, $payload, 'POST', $extras);

        //Prepare the response (error/ok)
        if (is_array($resumable_session_url)) {

            if ($resumable_session_url[0] === 'error') {
                $error_occurrs = true;
                $error_reason = $resumable_session_url[1];
                return array('error', 'Cloud Storage error: ' . $error_reason);
            }

        } else {

            $resumable_session_url = str_replace('\r\n', ' ', $resumable_session_url);
            preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $resumable_session_url, $match);

            return $match[0][0];
        }
    }

// SET CURL - UPLOAD REPORT GOOGLE CLOUD
    function upload_report_to_google_storage($resumable_session_url, $csv_string)
    {

        $headers = array('Content-Length: ' . strlen($csv_string));
        $endpoint = $resumable_session_url;
        $payload = $csv_string;

        $response = $this->set_curl($headers, $endpoint, $payload, 'PUT', null);

        return $response;

    }

//  Get service access token - Function that returns an access token either from db as is not expired yet or straight from the api request
    function get_storage_access_token($extraction)
    {

        //If the access token has expired
        $gcs_access_token = $extraction['global']['storage_data']['access_token'];
        $gcs_client = $extraction['global']['storage_data']['client'];
        $gcs_scope = $extraction['global']['storage_data']['scope'];
        $gcs_key = $extraction['global']['storage_data']['key'];

        if ($this->get_http_response_code('https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . $gcs_access_token) != "200") {
            $gcs_access_token = $this->get_service_account_access_token($gcs_client, $gcs_scope, $gcs_key);
            if (!isset($gcs_access_token)) {
                syslog(LOG_DEBUG, 'error storage access token:' . $gcs_access_token);
            }
        }

        return $gcs_access_token;
    }

//  Get service account access token - Function that returns an access token to make calls to google cloud storage
    function get_service_account_access_token($client, $scope, $key)
    {

        $iat = time();
        $endpoint = "https://www.googleapis.com/oauth2/v4/token";

        //Sign JWT
        $header = array('typ' => 'JWT', 'alg' => 'RS256');
        $jwt_data = array('iss' => $client, 'aud' => $endpoint, 'scope' => $scope, 'exp' => $iat + 3600, 'iat' => $iat);
        $signing_input = $this->base64_url_encode(json_encode($header)) . '.' . $this->base64_url_encode(json_encode($jwt_data));
        openssl_sign($signing_input, $signature, $key, 'SHA256');

        //Request to get the access token linked to a service account
        $jwt = $signing_input . '.' . $this->base64_url_encode($signature);
        $data = array("grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer", "assertion" => $jwt);

        $headers = array('Content-Type: application/x-www-form-urlencoded');
        $payload = http_build_query($data);

        //Access token
        $response = $this->set_curl($headers, $endpoint, $payload, 'POST', null);
        $access_token = json_decode($response)->access_token;

        return $access_token;

    }

// GET HTTP RESPONSE CODE - HELPER METHOD TO GET CODE FROM A GET REQUEST
    function get_http_response_code($endpoint)
    {
        $headers = get_headers($endpoint);
        return substr($headers[0], 9, 3);
    }

// BASE 64 URL ENCODE - HELPER METHOD THAT ENCODES STRING TO BASE64
    function base64_url_encode($input)
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }

// return  if values exists
    function return_safe($var, $default = null)
    {
        if (isset($var) && isset($str)) {
            return $str;
        } else {
            return isset($var) ? $var : $default;
        }
    }

    function return_isset(&$isset, $default = null) {
        return isset($isset) ? $isset : $default;
    }

// status log file - old version
    function status_log($data)
    {
        $file_path = "gs://api-jobs-files/status-stg-" . date('Y-m-d') . ".txt";
        if (file_exists($file_path)) {
            $historic_data = file_get_contents($file_path);
        } else {
            $historic_data = '';
        }
        $new_line = date('Y-m-d h:i:s') . " " . $data . "\n";
        file_put_contents($file_path, $new_line . $historic_data);
    }

// log using google sheet
    function result_log($extraction, $row)
    {
        $now = new DateTime();;
        array_unshift($row,  $now->format('d-m-Y'),  $now->format('H:i:s'));
        $extraction = $this->check_access_token($extraction, 'sheets');
        $sheet_id = $extraction['global']['google_sheet']['tmp_sheet_id'];
        $headers = array('Content-type: application/json', 'Authorization : Bearer ' . $extraction['global']['google_sheet']['access_token']);
        $endpoint = "https://content-sheets.googleapis.com/v4/spreadsheets/$sheet_id/values/A1:append?includeValuesInResponse=true&insertDataOption=INSERT_ROWS&valueInputOption=RAW&alt=json";
        $payload = json_encode(array("values" => array($row)));// double array
        $result = $this->set_curl($headers, $endpoint, $payload, 'POST', null, $extraction);
        //syslog(LOG_DEBUG, "sheets:" . $result);
        return $extraction;
    }
}