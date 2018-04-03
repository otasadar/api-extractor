<?php

/**
 * Common functions across all API's
 */
class helpers
{
    // Storage file with public permissions
    function file_put_contents_public($filename, $data)
    {
        $options = ['gs' => ['acl' => 'public-read']];
        $context = stream_context_create($options);
        file_put_contents($filename, $data, 0, $context);
    }

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
            $this->gae_log(LOG_DEBUG, 'error access token' . $access_token);
            return false;
        }
    }

    function check_access_token($extraction, $case = null)
    {
        $now = new DateTime();
        switch ($case) {

            case "google_transfer":
                $access_token_datetime = $extraction['global']['google_storage_transfer']['access_token_datetime'];
                break;
            case "sheets":
                $access_token_datetime = $extraction['global']['google_sheet']['access_token_datetime'];
                break;
            case "tasks":
                $access_token_datetime = $extraction['global']['tasks']['access_token_datetime'];
                break;
            default:
                $access_token_datetime = $extraction['access_token_datetime'];
        }

        $minutes = $this->get_minutes_diff($access_token_datetime);

        if ($minutes > 50) {
            $client_id = $extraction['global']['google']['client_id'];
            $client_secret = $extraction['global']['google']['client_secret'];

            switch ($case) {
                case "google_transfer":
                    $refresh_token = $extraction['global']['google_storage_transfer']['refresh_token'];
                    $extraction['global']['google_storage_transfer']['access_token'] = $this->get_access_token($client_id, $client_secret, $refresh_token);
                    $extraction['global']['google_storage_transfer']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
                    break;
                case "sheets":
                    $refresh_token = $extraction['global']['google_sheet']['refresh_token'];
                    $extraction['global']['google_sheet']['access_token'] = $this->get_access_token($client_id, $client_secret, $refresh_token);
                    $extraction['global']['google_sheet']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
                    break;
                case "tasks":
                    $refresh_token = $extraction['global']['tasks']['refresh_token'];
                    $extraction['global']['tasks']['access_token'] = $this->get_access_token($client_id, $client_secret, $refresh_token);
                    $extraction['global']['tasks']['access_token_datetime'] = $now->format('Y-m-d H:i:s');
                    break;
                default:
                    $this->gae_log(LOG_DEBUG, 'access_token');
                    $extraction['access_token'] = $this->get_access_token($client_id, $client_secret, $extraction['refresh_token']);
                    $extraction['access_token_datetime'] = $now->format('Y-m-d H:i:s');
            }


        }

        return $extraction;

    }

    function get_minutes_diff($to_date) {
        $now = new DateTime();
        $start_date = new DateTime($to_date);
        $since_start = $start_date->diff(new DateTime($now->format('Y-m-d H:i:s')));

        $minutes = $since_start->days * 24 * 60;
        $minutes += $since_start->h * 60;
        $minutes += $since_start->i;
        return $minutes;
    }

    function get_seconds_diff($to_date) {
        $now = new DateTime();
        $start_date = new DateTime($to_date);
        $since_start = $start_date->diff(new DateTime($now->format('Y-m-d H:i:s')));

        $seconds = $since_start->days * 24 * 60;
        $seconds += $since_start->h * 60;
        $seconds += $since_start->i * 60;
        $seconds += $since_start->s;
        return $seconds;
    }

    //  SET CURL GENERAL - HELPER METHOD THAT ISSUES A CURL REQUEST
    function set_curl($headers, $endpoint, $payload, $type, $extras = null, $range = null)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $endpoint);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 600);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);

        if (is_array($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        } else {
            $this->gae_log(LOG_DEBUG, "curl without headers" . $endpoint);
        }
        if (isset($extras)) {
            foreach ($extras as $extra) {
                curl_setopt($curl, $extra[0], $extra[1]);
            }
        }
        if (isset($range)) {
            curl_setopt($curl, CURLOPT_RANGE, $range);
        }

        $response_body = curl_exec($curl);
        $response_headers = curl_getinfo($curl);
        curl_close($curl);


        if (strpos($response_headers['http_code'], '30') !== false) {

            $this->gae_log(LOG_DEBUG, "URL size before downlodad:" . $this->get_curl_remote_file_size($response_headers['redirect_url']));
            $response_body = $this->set_simple_curl($response_headers['redirect_url']);
            $this->gae_log(LOG_DEBUG, "simple curl after downlodad:" . mb_strlen($response_body));
            return $response_body;

        } else if (strpos($response_headers['http_code'], '20') !== false) {

            return $response_body;

        } else {
            $this->gae_log(LOG_DEBUG, "error curl :" . json_encode($response_headers));
            $this->gae_log(LOG_DEBUG, "error curl :" . $endpoint);
            $this->gae_log(LOG_DEBUG, "error curl :" . $response_body);
            $this->gae_log(LOG_DEBUG, "error curl :" . $type);
            return array($response_headers['http_code'], $response_body, $endpoint, $response_headers);
        }
    }

    // Simple CURL for DCM URL extractions
    function set_simple_curl($url, $range = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (isset($range)) {
            curl_setopt($ch, CURLOPT_RANGE, $range);
        }
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $this->gae_log(LOG_DEBUG, "error set_simple_curl" . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

    // GET CURL RAW WITH HEADER - regular functions only returns standards headers. Yandex API use custom headers
    function set_curl_raw($headers, $endpoint, $payload, $type, $extras = null, $range = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);

        $header_body =  curl_exec($ch);

        //$this->gae_log(LOG_DEBUG, "set_curl_raw".$header_body);

        $header =  substr($header_body, 0, strpos($header_body, "\r\n\r\n" ));
        $body =  substr($header_body, (strpos($header_body, "\r\n\r\n" )+4));

        $header = explode("\r\n", $header );
        foreach ($header as $key => $row){
            if (strpos($row, ':') !== false) {
                $row = explode(":", $row );
                $header[$row[0]] = trim($row[1]);
                unset($header[$key]);
            }
        }

        $result = ["header" => $header, "body" => $body];

        curl_close($ch);
        return $result;
    }

    // Get file size with only URL and without downloading file, only using headers
    function get_curl_remote_file_size($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_NOBODY, TRUE);

        curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);
        return $size;
    }

    // SET CURL - CREATE AND UPDATE CSV FILE
    function create_csv_file($extraction, $file_path = null)
    {

        $csv_string = $extraction['csv_output'];
        $storage_data = $extraction['global']['google_storage'];
        $bucket = $extraction['global']['google_storage']['bucket'];

        $access_token = $this->get_storage_access_token($extraction);


        if ($access_token) {
            $resumable_session_url = $this->get_google_storage_session_url($extraction, $bucket, $access_token, $file_path);
            if (!is_array($resumable_session_url)) {

                $report_metadata_latest = $this->upload_report_to_google_storage($resumable_session_url, $csv_string);

                if (!is_array($report_metadata_latest)) {

                    return true;

                } else {

                    return false;
                }

            } else {
                $this->gae_log(LOG_DEBUG, 'Report metadata has not been updated to the Data Base.');
            }
        } else {
            $this->gae_log(LOG_DEBUG, 'access token not found');
        }
    }

    // SET CURL - GOOGLE CLOUD SESSION URL
    function get_google_storage_session_url($extraction, $bucket, $access_token, $file_path = null)
    {
        if (!$file_path) {
            $file_path = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}.csv";
        }

        $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
        $version = $extraction['global']['google_storage']['api_version'];
        $endpoint = "https://www.googleapis.com/upload/storage/$version/b/$bucket/o?uploadType=resumable&predefinedAcl=publicRead&name=$file_path";
        $extras = array(array(CURLOPT_HEADER, 1));
        $payload = json_encode(['cacheControl' => 'public, max-age=0, no-transform']);

        $this->gae_log(LOG_DEBUG, 'Cloud storage: ' . $endpoint);

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

    // Set temporal file, combine files and delete temporal
    function storage_insert_combine_delete($extraction)
    {
        $bucket = $extraction['global']['google_storage']['bucket'];
        $tmp_object = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}_tmp.csv";
        $bucket_tmp_path = "gs://$bucket/$tmp_object";
        file_put_contents($bucket_tmp_path, $extraction['csv_output']);

        $response = $this->combine_tmp_google_storage($extraction);

        if (!is_array($response)) {
            unlink($bucket_tmp_path);

            if (is_array($response)) {
                if ($response[0] !== 204) {
                    $this->gae_log(LOG_DEBUG, 'error storage_insert_combine_delete');
                }
            }
        }

    }

    // Combine storage  file
    function combine_tmp_google_storage($extraction)
    {

        $bucket = $extraction['global']['google_storage']['bucket'];

        $tmp_object = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}_tmp.csv";
        $final_object = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}.csv";
        $final_object_encode = rawurlencode($final_object);


        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization: Bearer ' . $access_token,
            'Accept: application/json',
            'Content-Type: application/json');
        $payload = '{"sourceObjects":[{"name":"' . $final_object . '"},{"name":"' . $tmp_object . '"}]}';
        $version = $extraction['global']['google_storage']['api_version'];
        $endpoint = "https://www.googleapis.com/storage/$version/b/$bucket/o/$final_object_encode/compose";
        $response = $this->set_curl($headers, $endpoint, $payload, 'POST');

        //$this->gae_log(LOG_DEBUG, "combine:$response");

        return $response;
    }

    // Delete temporal storage file
    function delete_tmp_google_storage($extraction)
    {
        $tmp_file_path = rawurlencode("{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}.csv");

        $this->gae_log(LOG_DEBUG, "delete file : tmp_object:$tmp_file_path");

        $bucket = $extraction['global']['google_storage']['bucket'];
        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization: Bearer ' . $access_token,
            'Accept: application/json');
        $version = $extraction['global']['google_storage']['api_version'];
        $endpoint = "https://www.googleapis.com/storage/$version/b/$bucket/o/$tmp_file_path";
        $response = $this->set_curl($headers, $endpoint, null, 'DELETE', null);
        return $response;
    }

    //  Get service access token - Function that returns an access token either from db as is not expired yet or straight from the api request
    function get_storage_access_token($extraction)
    {

        //If the access token has expired
        $gcs_access_token = $extraction['global']['google_storage']['access_token'];
        $gcs_client = $extraction['global']['google_storage']['client'];
        $gcs_scope = $extraction['global']['google_storage']['scope'];
        $gcs_key = $extraction['global']['google_storage']['key'];
        $api_version = $extraction['global']['google_oauth']['api_version'];

        if ($this->get_http_response_code("https://www.googleapis.com/oauth2/$api_version/tokeninfo?access_token=$gcs_access_token") != "200") {
            $gcs_access_token = $this->get_service_account_access_token($gcs_client, $gcs_scope, $gcs_key, $extraction);
            if (!isset($gcs_access_token)) {
                $this->gae_log(LOG_DEBUG, 'error storage access token:' . $gcs_access_token);
            }
        }

        return $gcs_access_token;
    }

    //  Get service account access token - Function that returns an access token to make calls to google cloud storage
    function get_service_account_access_token($client, $scope, $key, $extraction)
    {

        $iat = time();
        $api_version = $extraction['global']['google_oauth']['api_version'];
        $endpoint = "https://www.googleapis.com/oauth2/$api_version/token";

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

    // Return any string only if exists without returning undefined message
    function return_isset(&$isset, $default = null)
    {
        return isset($isset) ? $isset : $default;
    }

    // log using google sheet
    function live_log($extraction, $row)
    {

        $row = $this->live_log_add_general($extraction, $row);

        if (isset($extraction['global']['google_sheet']['last_request'])) {
            // limit 'USER-100s' of service 'sheets.googleapis.com'
            $total_task = $extraction['global']['items_counter'];
            $seconds_diff = $this->get_seconds_diff($extraction['global']['google_sheet']['last_request']) - $total_task;

            if (!isset($extraction['global']['google_sheet']['rows'])) {
                $extraction['global']['google_sheet']['rows'] = [];
            }
            array_push($extraction['global']['google_sheet']['rows'], $row);
            if ($seconds_diff > $total_task+1) {
                $row = $extraction['global']['google_sheet']['rows'];
                $extraction = $this->live_log_request($extraction, $row);
                $extraction['global']['google_sheet']['rows'] = [];
            }

        } else {
            // first time
            $row =  array($row); // double array
            $extraction = $this->live_log_request($extraction, $row);
        }


        return $extraction;
    }

    // add general values to each row of live loggin
    function live_log_add_general($extraction, $row) {
        // live log common
        if (isset($extraction['report_type'])) {
            $report_type = $extraction['report_type'];
        } else {
            $report_type = 'N/A';
        }

        $now = new DateTime();
        array_unshift($row,
            $now->format('d-m-Y'),
            $now->format('H:i:s'),
            $extraction['api'],
            $extraction['task_name'],
            $report_type);
        return $row;

    }

    // final request for live logging
    function live_log_request($extraction, $row) {

        $now = new DateTime();
        $extraction = $this->check_access_token($extraction, 'sheets');
        $sheet_id = $extraction['global']['google_sheet']['sheet_id'];
        $headers = array('Content-type: application/json', 'Authorization : Bearer ' . $extraction['global']['google_sheet']['access_token']);
        $api_version = $extraction['global']['google_sheet']['api_version'];
        $endpoint = "https://sheets.googleapis.com/$api_version/spreadsheets/$sheet_id/values/A1:append?includeValuesInResponse=true&insertDataOption=INSERT_ROWS&valueInputOption=RAW&alt=json";
        $payload = json_encode(array("values" => $row));// double array
        $this->set_curl($headers, $endpoint, $payload, 'POST', null);
        $extraction['global']['google_sheet']['last_request'] = $now->format('Y-m-d H:i:s');
        return $extraction;
    }

    // Google Cloud log
    function gae_log($priority, $message)
    {
        if ($_SERVER['HTTP_HOST'] !== 'localhost:8080') {
            syslog($priority, $message);
        }
    }

    // Google App Engine Tasks
    function get_current_tasks($extraction)
    {

        //$extraction = $this->check_access_token($extraction, 'tasks');
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $refresh_token = $extraction['global']['tasks']['refresh_token'];

        $access_token = $this->get_access_token($client_id, $client_secret, $refresh_token);
        $project_id = $extraction['global']['project'];
        $location_id = $extraction['global']['location'];
        $queue_id = $extraction['global']['queue'];

        $version = $extraction['global']['tasks']['api_version'];
        $endpoint = "https://cloudtasks.googleapis.com/$version/projects/$project_id/locations/$location_id/queues/$queue_id/tasks?access_token=$access_token";
        $this->gae_log(LOG_DEBUG, "tasks-endpoint:" . $endpoint);
        $response = $this->set_curl(null, $endpoint, null, 'GET', null);

        return $response;
    }

    // Convert bytes to Mb
    function bytesToMBytes($bytes, $precision = 1)
    {
        return round($bytes / 1000000, $precision) . " Mb";
    }

    // Put URLs content to buckets
    function save_urls_data_to_buckets($extraction)
    {
        // Get URLS extracted from API process
        $bucket = $extraction['global']['google_storage']['bucket'];
        $search_str = "{$extraction['extraction_group']}/input/{$extraction['api']}/url";
        $response = $this->get_urls_to_transfer($extraction, $search_str);
        $this->gae_log(LOG_DEBUG, "search_str:" . $search_str);
        $this->gae_log(LOG_DEBUG, "url-to-transfer:" . json_encode($response));
        $extraction = $this->live_log($extraction, Array("GET-URLS"));

        // Read URL for get MD5 and size
        $response = $this->get_urls_md5($response);
        $this->gae_log(LOG_DEBUG, "get-md5:" . $response);
        $extraction = $this->live_log($extraction, Array("GET-URLS-MD5"));

        // Save TSV file
        $this->file_put_contents_public("gs://$bucket/{$extraction['extraction_group']}/input/{$extraction['api']}/tsv", $response);
        $extraction = $this->live_log($extraction, Array("SET-TSV"));

        // start transfer from URL source to bucket destination using API Google Transfer
        $response = $this->transfer_urls_to_bucket($extraction);
        $this->gae_log(LOG_DEBUG, "transfer-response:" . json_encode($response));
        $extraction = $this->live_log($extraction, Array("TRANSFER","START"));

        // check status until === ENABLED
        $status = $this->check_status_transfer_urls_to_bucket($extraction, $response);
        $extraction = $this->live_log($extraction, Array("TRANSFER",$status));
        // to do : if status !== SUCCESS die process


        // Move tmp transferred object to final location and delete tmp
        $search_str = "storage.googleapis.com";
        $destination_folder = "{$extraction['extraction_group']}/input/{$extraction['api']}";
        $this->move_buckets_files($extraction, $search_str, $destination_folder);
        $extraction = $this->live_log($extraction, Array("MOVE-OBJECTS"));

        return $extraction;

    }

    // Get all URLS to transfer
    function get_urls_to_transfer($extraction, $search_str)
    {
        // find all files
        // get urls from buckets
        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization : Bearer ' . $access_token, 'Accept: application/json');
        $version = $extraction['global']['google_storage']['api_version'];
        $bucket = $extraction['global']['google_storage']['bucket'];
        $path = rawurlencode($search_str);
        $endpoint = "https://www.googleapis.com/storage/$version/b/$bucket/o?prefix=$path";
        $response = $this->set_curl($headers, $endpoint, null, 'GET', null);
        return json_decode($response);
    }

    // Get MD5 from VM
    function get_urls_md5($response)
    {

        $tsv_data = [];
        foreach ($response->items as $item) {

            //$object = explode('/', $item->selfLink);
            //$object = end($object);
            //$object = rawurldecode($object);
            $gs_url = "gs://$item->bucket/$item->name";
            $this->gae_log(LOG_DEBUG, "check link: " . $gs_url);
            $url = file_get_contents($gs_url);
            $url = trim($url);
            unlink($gs_url);

            $this->gae_log(LOG_DEBUG, "check content link" . $url);

            $headers = array('content-type: application/x-www-form-urlencoded');
            $endpoint = 'http://35.200.161.162/helpers/md5-hash-and-size.php';
            $payload = 'url=' . rawurlencode($url);
            $curl_response = json_decode($this->set_curl($headers, $endpoint, $payload, 'POST', null), true);
            $this->gae_log(LOG_DEBUG, "check responsse md5" . json_encode($curl_response));

            if (empty($tsv_data)) $tsv_data[] = 'TsvHttpData-1.0';
            $tsv_data[] = "$url\t{$curl_response['size']}\t{$curl_response['hash']}";
        }
        // return array urls-size-md5
        return implode("\n", $tsv_data);

    }

    // Split date in equal parts
    function split_dates_equal($min, $max, $parts, $output = "Y-m-d")
    {
        $dataCollection[] = date($output, strtotime($min));
        $diff = (strtotime($max) - strtotime($min)) / $parts;
        $convert = strtotime($min) + $diff;

        for ($i = 1; $i < $parts; $i++) {
            $dataCollection[] = date($output, $convert);
            $convert += $diff;
        }
        $dataCollection[] = date($output, strtotime($max));
        return $dataCollection;
    }

    // Return numer of month between two dates
    function date_difference($startDate, $endDate, $format)
    {
        $d1 = new DateTime($startDate);
        $d2 = new DateTime($endDate);
        $interval = $d2->diff($d1);
        return (int)$interval->format('%' . $format);
    }

    // Google API storage - Transfer URL to bucket
    function transfer_urls_to_bucket($extraction)
    {

        //Refresh access token
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $refresh_token = $extraction['global']['google_storage_transfer']['refresh_token'];
        $access_token = $this->get_access_token($client_id, $client_secret, $refresh_token);
        $bucket = $extraction['global']['google_storage']['bucket'];

        //Call headers
        $headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);

        //End point
        $api_version = $extraction['global']['google_storage_transfer']['api_version'];
        $endpoint = "https://storagetransfer.googleapis.com/$api_version/transferJobs";
        $random = rand();

        //Payload data
        $payload = array(
            'description' => $extraction['api']."-".$extraction['extraction_group'],
            'projectId' => $extraction['global']['project'],
            'transferSpec' =>
                array(
                    'httpDataSource' =>
                        array(
                            'listUrl' => "https://storage.googleapis.com/$bucket/{$extraction['extraction_group']}/input/{$extraction['api']}/tsv?random=$random",
                        ),
                    'gcsDataSink' =>
                        array(
                            'bucketName' => $extraction['global']['google_storage']['bucket'],
                        ),
                    'transferOptions' =>
                        array(
                            'overwriteObjectsAlreadyExistingInSink' => true,
                        ),
                ),
            'schedule' =>
                array(
                    'scheduleStartDate' =>
                        array(
                            'year' => (int)date("Y"),
                            'month' => (int)date("m"),
                            'day' => (int)date("d"),
                        ),
                    'scheduleEndDate' =>
                        array(
                            'year' => (int)date("Y"),
                            'month' => (int)date("m"),
                            'day' => (int)date("d"),
                        ),
                ),
            'status' => 'ENABLED',
        );
        $this->gae_log(LOG_DEBUG, "transferjob payload" . json_encode($payload));

        //CURL request
        $response = $this->set_curl($headers, $endpoint, json_encode($payload), 'POST', null);
        return json_decode($response);

    }

    // Google transfer status
    function get_status_transfer_urls_to_bucket($extraction, $response)
    {
        //Refresh access token
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $refresh_token = $extraction['global']['google_storage_transfer']['refresh_token'];
        $access_token = $this->get_access_token($client_id, $client_secret, $refresh_token);

        //Call headers
        $headers = array('Authorization: Bearer ' . $access_token, 'Accept: application/json');

        //End point
        $filter = '{"project_id" : "' . $extraction['global']['project'] . '", "job_names": ["' . $response->name . '"]}';
        $filter = rawurlencode($filter);
        $api_version = $extraction['global']['google_storage_transfer']['api_version'];
        $endpoint = "https://storagetransfer.googleapis.com/$api_version/transferOperations?filter=$filter";

        $response2 = $this->set_curl($headers, $endpoint, null, 'GET');
        $this->gae_log(LOG_DEBUG, "response2-status:" . $response2);

        $response2 = json_decode($response2);
        if(isset($response2->operations[0]->metadata->status)) {
            return $response2->operations[0]->metadata->status;
        } else {
            return null;
        }


    }

    // Recursive functions for retrieve enable status
    function check_status_transfer_urls_to_bucket($extraction, $response)
    {
        $status = $this->get_status_transfer_urls_to_bucket($extraction, $response);

        if ($status === 'IN_PROGRESS') {
            sleep(5);
            $extraction = $this->live_log($extraction, Array("TRANSFER", $status));
            return $this->check_status_transfer_urls_to_bucket($extraction, $response);
        }
        else {
            return $status;
        }
    }

    // Copy file into buckets
    function storage_copy_object($extraction, $sourceBucket, $sourceObject, $destinationBucket, $destinationObject)
    {
        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization : Bearer ' . $access_token, 'Accept: application/json', 'Content-Type: application/json');
        $version = $extraction['global']['google_storage']['api_version'];
        $sourceBucket = rawurlencode($sourceBucket);
        $sourceObject = rawurlencode($sourceObject);
        $destinationBucket = rawurlencode($destinationBucket);
        $destinationObject = rawurlencode($destinationObject);

        $endpoint = "https://www.googleapis.com/storage/$version/b/$sourceBucket/o/$sourceObject/copyTo/b/$destinationBucket/o/$destinationObject";
        $response = $this->set_curl($headers, $endpoint, null, 'POST');
        return json_decode($response);
    }

    // Remove file into buckets
    function storage_delete_object($extraction, $bucket, $object)
    {
        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization : Bearer ' . $access_token, 'Accept: application/json');
        $version = $extraction['global']['google_storage']['api_version'];
        $bucket = rawurlencode($bucket);
        $object = rawurlencode($object);

        $endpoint = "https://www.googleapis.com/storage/$version/b/$bucket/o/$object";
        $response = $this->set_curl($headers, $endpoint, null, 'DELETE');
        // If successful, this method returns an empty response body.
        return $response;
    }

    // Move file between buckets
    function move_buckets_files($extraction, $search_str, $destination_folder = null)
    {

        $bucket = $extraction['global']['google_storage']['bucket'];

        // copying transfer file to input folder
        $this->gae_log(LOG_DEBUG, "search_str" . $search_str);
        $response = $this->get_urls_to_transfer($extraction, $search_str);
        $this->gae_log(LOG_DEBUG, "file_list_bucket" . json_encode($response));

        foreach ($response->items as $item) {

            $sourceObject = explode('/', $item->selfLink);
            $sourceObject = end($sourceObject);
            $sourceObject = rawurldecode($sourceObject);

            $destinationObject = explode('/', $sourceObject);
            $destinationObject = end($destinationObject);
            $destinationObject = explode('EOF', $destinationObject);
            $destinationObject = "$destination_folder/$destinationObject[0].csv";

            $this->storage_copy_object($extraction, $bucket, $sourceObject, $bucket, $destinationObject);
            $this->gae_log(LOG_DEBUG, "copying file:" . $destinationObject);

        }

        //deleting transfer files
        $search_str = "storage.googleapis.com";
        $response = $this->get_urls_to_transfer($extraction, $search_str);
        foreach ($response->items as $item) {
            $sourceObject = explode('/', $item->selfLink);
            $sourceObject = end($sourceObject);
            $sourceObject = rawurldecode($sourceObject);
            $this->storage_delete_object($extraction, $bucket, $sourceObject);
            $this->gae_log(LOG_DEBUG, "deleting: " . $item->selfLink);
        }
        // deleting tsv file
        unlink("gs://$bucket/{$extraction['extraction_group']}/input/{$extraction['api']}/tsv");

    }

    // Check and control error response between request for avoid continue
    function check_n_control($response, $extraction) {
        if (is_array($response)) {
            $extraction['current']['error'] = true;
            $extraction['current']['http_code'] = $response[0];
            $extraction['current']['error_response'] = $response[1];
            $extraction['current']['endpoint'] = $response[2];
            $extraction['current']['info'] = $response[3];
            $log_values = array(
                "CURL ERROR",
                $extraction['current']['http_code'],
                $extraction['current']['endpoint'],
                $extraction['current']['error_response'],
                json_encode($extraction['current']['info']));
            $extraction = $this->live_log($extraction, $log_values);
            return $extraction;
        }
        return $extraction;

    }

    // Copy objects in same bucket
    function copy_object_google_storage($extraction, $sourceObject, $destinationObject )
    {

        $bucket = $extraction['global']['google_storage']['bucket'];

        $sourceObject = rawurlencode($sourceObject);
        $destinationObject = rawurlencode($destinationObject);
        $sourceBucket = rawurlencode($bucket);
        $destinationBucket = rawurlencode($bucket);


        $access_token = $this->get_storage_access_token($extraction);
        $headers = array('Authorization: Bearer ' . $access_token,
            'Accept: application/json',
            'Content-Type: application/json');
        $version = $extraction['global']['google_storage']['api_version'];


        $endpoint = "https://www.googleapis.com/storage/$version/b/$sourceBucket/o/$sourceObject/copyTo/b/$destinationBucket/o/$destinationObject";
        $response = $this->set_curl($headers, $endpoint, null, 'POST');
        $response = json_encode($response);
        $this->gae_log(LOG_DEBUG, "copied bucket object");

        return $response;
    }

    // Rename tmp file to final name (delete & create)
    function rename_tmp_to_final_file($extraction) {

        $bucket = $extraction['global']['google_storage']['bucket'];
        $sourceObject = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}.csv";
        $extraction['extraction_name'] = str_replace('-tmp-'.$extraction['extraction_id'], '', $extraction['extraction_name']);
        $destinationObject = "{$extraction['extraction_group']}/input/{$extraction['api']}/{$extraction['extraction_name']}.csv";
        $response = $this->copy_object_google_storage($extraction, $sourceObject, $destinationObject );

        if (!isset($response['error'])) unlink("gs://$bucket/$sourceObject");

    }

    // Actions for VM api job
    function actions_jobs_executor_vm($extraction, $action) {

        //Refresh access token
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $refresh_token = $extraction['global']['google_compute']['refresh_token'];
        $api_version = $extraction['global']['google_compute']['api_version'];

        $access_token = $this->get_access_token($client_id, $client_secret, $refresh_token);

        $headers = array('content-type: application/json', 'authorization : Bearer ' . $access_token);
        $endpoint = "https://www.googleapis.com/compute/$api_version/projects/annalect-api-jobs/zones/asia-south1-a/instances/jobs-executor-vm/$action";

        return $this->set_curl($headers, $endpoint, null, 'POST');
    }

}