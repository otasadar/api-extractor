<?php
ini_set('display_errors', 1);

$client_id = '1072155568501-rpcfah9qpkg9jro4c6c9sh62go6pm7oe.apps.googleusercontent.com';
$client_secret = 'PAx5fz386w0groUL8JFgdVuQ';
$refresh_token = '1/ymsZ6LP831oYWPc71ULlMt5hQG7zxs1nJG3SISJL7birTQexP-s4qh2O1RdHXIjH';
$access_token = update_access_token($client_id, $client_secret, $refresh_token);
$storage_data = '{"client":"annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com","access_token":"ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A","key":"-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n","scope":"https:\/\/www.googleapis.com\/auth\/devstorage.full_control https:\/\/www.googleapis.com\/auth\/cloud-taskqueue","bucket":"annalect-dashboarding"}';


$api_response = json_encode('{
 "kind": "dfareporting#file",
 "reportId": "117205113",
 "id": "671192440",
 "lastModifiedTime": "1516778331000",
 "status": "REPORT_AVAILABLE",
 "fileName": "",
 "format": "CSV",
 "dateRange": {
  "kind": "dfareporting#dateRange",
  "startDate": "2018-01-16",
  "endDate": "2018-01-22"
 },
 "urls": {
  "browserUrl": "https://www.google.com/analytics/dfa/downloadFile?id=117205113:671192440",
  "apiUrl": "https://www.googleapis.com/dfareporting/v3.0/reports/117205113/files/671192440?alt=media"
 }}', TRUE);

date_default_timezone_set('Asia/Dubai');
$datetime = new DateTime();

//print $datetime->format('Y/m/d H:i:s');


$dashboard['dashboard_name'] = 'dashboard1';
$dashboard['api'] = 'dcm';
$dashboard['file_name'] = 'testing-curl.csv';



//create_csv_file('123', 'luckyfile.csv', $storage_data);


$headers = array("Authorization: Bearer $access_token");
$endpoint = "https://www.googleapis.com/dfareporting/v3.0/reports/118515072/files/676761916?alt=media";
echo "<pre>";
var_dump($headers);
var_dump($endpoint);

$curl_response = set_curl($headers, $endpoint, null, 'GET', null);
$curl_response = json_decode($curl_response);

syslog(LOG_DEBUG, "dcm_get_report_url ".json_encode($curl_response) );









//  SET_APIS - INVOKE API FUNCTION TO LOAD THE ACCOUNTS LISTS
function update_access_token($client_id, $client_secret, $refresh_token)
{

    //POST request
    $headers = array('Content-type: application/x-www-form-urlencoded');
    $endpoint = 'https://www.googleapis.com/oauth2/v4/token';
    $payload = 'client_id=' . $client_id . '&client_secret=' . $client_secret . '&refresh_token=' . $refresh_token . '&grant_type=refresh_token';
    $access_token = set_curl($headers, $endpoint, $payload, 'POST', null);

    if ($access_token) {

        return json_decode($access_token)->access_token;

    } else {
        syslog(LOG_DEBUG, 'error access token' . $access_token);
        return false;
    }
}

//  SET CURL ADWORDS - HELPER METHOD THAT ISSUES A CURL REQUEST
function set_curl_adwords($headers, $endpoint, $payload, $type, $extras)
{

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($curl, CURLOPT_TIMEOUT, 60);

    if ($extras) {
        foreach ($extras as $extra) {
            curl_setopt($curl, $extra[0], $extra[1]);
        }
    }

    $response = curl_exec($curl);
    $info = curl_getinfo($curl);

    return $response;
}

//  SET CURL GENERAL - HELPER METHOD THAT ISSUES A CURL REQUEST
function set_curl($headers, $endpoint, $payload, $type, $extras = null)
{

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $endpoint);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $type);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 600);
    if (isset($headers)) {
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    }
    if (isset($payload)) {
        curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
    }
    if (isset($extras)) {
        foreach ($extras as $extra) {
            curl_setopt($curl, $extra[0], $extra[1]);
        }
    }

    $response = curl_exec($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);

    if (strpos($info['http_code'], '30') !== false ) {
        syslog(LOG_DEBUG, "error curl 30:". json_encode($info));
        var_dump($info['redirect_url']);
        var_dump($response);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $info['redirect_url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        //echo strlen($result);
        $storage_data = '{"client":"annalect-api-jobs@annalect-api-jobs.iam.gserviceaccount.com","access_token":"ya29.c.ElwIBQ9QzuTBMDhi1oW74_ibk5T99f_g-kvJ5_fTuJOVK9Flr-Fk3aJWcyFPyYoQgTwnO6_DtMj_kzhUFBSezIJy120LplyFWrn6q1sinsDOmDo6BP5IrJqlO4fi5A","key":"-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQC8XEBqrBmjW7mn\n8O+5k3DvnRxGsw1ZPPNVFEb3wOoYdmnxPQ0YInUVbm6axWwh2aMc6unB+yBSgKkF\nL+i/4R21m+xPUX84IApJRmYa1zFg4eqJU/XUZfvdKbXLNnVn7KlMhrTkoFp10ZFH\ngyt/0AfqE5GqX0sJ3rhwZxP5bmsWZnC0InEtVxlg/QvWc1OLyuLIyedU+gg9KAaG\nDJrx3aBGCbzxl5f+BLhdWeByx3JTouand2kQkuVQDAxClA0RfB7DPVfMRPgDV8sM\nXV08l5cOnXT6FCK9lNoMRHr1/s8kcrABJcBp04+9aPYzIwSsRU3amtnQLOjlLBm8\nCc9k/AnLAgMBAAECggEACeWg/tnfGP3DUgcvWWVdXEG5kB9tVqCEupYvqmnpAvMJ\n2wKVswxwcXlVd21jIp4wW18efDJJPvyCpQKg7KAT8wxnzL0f/Z91WudHiyZ+cjQ8\nog2Wz5uyMas04aIRZi4BsUMsswyX5DPoOcUzHmqcV9jCYRzTsQeSUlmgK415M/qD\n4vKKDebnH287+qJk1th+pg1aAUM1lJvXJGkDUAlKkWwEo+yRMVYdzz6TJHQiyD9B\nJbB9DsL0IInLHpxQxApl8e9bFV61IYfNlmo8GWrRR82EsbT+SantY4gHf1BP3vtv\nClJrGDZwKRjnZx4kCDmXKc5oMOB1cn4/nOe5WSjSTQKBgQDcI2KQGW07AQhRPGg+\nqoMNOX4UZjJCe1HanrAnOKbudBkBRA9lqp0jW5IYS9mt2RBgsM8Y1HhEUFmmyOHA\nukQtMk6dMq8h2mI8vJGjiEQeIB+TcwbINZJIokAud/M7LqNNnTE4oaYm2bnriayu\nkhDIYQylZIbzke5Y1YRVIGvKhwKBgQDbC5tW6m7w0vAgRodGAL1x+U8qRvjsw3pg\nXwqyBOyapXoTvcw2MuTNS4ADGpq1eGBjBsehb5XvW14d7WqHfcDPYXq3IE9vB1Ls\nATp+0D9/IVt13LfvGBIaD0AH/zr1N1IdYZRMtOuNIQMMl+B8qL+pnPmE4Ct0RPPs\nTirotoTDnQKBgQDQNrvL9fDFxUU7qPokg5yuznk9DChvjzqtoDiW8FOb6L2Z3+j8\nTTKRtdPqHRwH/e4qtjE7mAMlAia5xPkaFFPVt+Z5cu4JBAi0z9qkpYdgQxv6l+qL\nRXhWMPipuxSZHpShHZPnr6V6y6a5bJ+jAk7TaE/Qw9OM37Nj3Jhs99xcUwKBgCmf\nMfw4/a2rF0+6txeZKmZOzjklVUV/+2/2f0zGXMMh8Glx5iziTNGpqABu/LjAz+fh\nMOu/DUl3HhInu9dVEN8XEb9cV1usk5gev6O7JGWezAdAUn8PHtluzmb2m5he066b\njRdqRVwCytaIwXJOimTLXCpggkFMnODpFYQ0slONAoGBAIlolThayqWVasEdX0KR\ncmYV6h5KmT/trjhKpMlXz0bPvegZpF7XFbodKF+0eA1gV5MmnDEQy4epGhD0zRt3\noKyoM08zyFLqpKZqxl+5gTyqbEbzbT/tuo6rydad/01IbWlcRLaNpBfmsSCdEHO3\nTqeSqspjDoxfvQISvBAfHIzP\n-----END PRIVATE KEY-----\n","scope":"https:\/\/www.googleapis.com\/auth\/devstorage.full_control https:\/\/www.googleapis.com\/auth\/cloud-taskqueue","bucket":"annalect-dashboarding"}';
        create_csv_file($result, 'luckyfile.csv', $storage_data);

        //set_curl(null, $info['redirect_url'], null, $type, null);
    }
    else if ($response === false || $info['http_code'] != 200) {
        syslog(LOG_DEBUG, "error curl :". $info['http_code'] );
        syslog(LOG_DEBUG, "error curl :". json_encode($info));
        syslog(LOG_DEBUG, "error curl :". $endpoint );
        syslog(LOG_DEBUG, "error curl :". implode(",", $headers) );
        syslog(LOG_DEBUG, "error curl :". $response );
        syslog(LOG_DEBUG, "error curl :". $type );
        // todo  remove this die
        die;

    } else {
        return $response;
    }
}

//  SET CURL - CREATE AND UPDATE CSV FILE
function save_file_gstorage($csv_string, $dashboard, $storage_data, $mode = 'new')
{
    $access_token = get_access_token(json_decode($storage_data));


    if ($access_token) {

        $resumable_session_url = get_gstorage_session_url($dashboard, json_decode($storage_data)->bucket, $access_token);

        if (!is_array($resumable_session_url)) {
            
            if ($mode === 'new') {
                $report_metadata_latest = write_data_gstorage($resumable_session_url, $csv_string);
            }
            if ($mode === 'add') {
                $report_metadata_latest = read_n_write_data_gstorage($dashboard, $csv_string, $access_token, json_decode($storage_data)->bucket);
            }

            if (!is_array($report_metadata_latest)) {

                return true;

            } else {

                return false;
            }

        } else {

            return array('error', 'Report metadata has not been updated to the Data Base.');
        }

    } else {

        return array('error', 'access token not found');
    }
}

//  SET CURL - GOOGLE CLOUD SESSION URL
function get_gstorage_session_url($dashboard, $bucket, $access_token)
{
    $file_name = urlencode (  "{$dashboard['dashboard_name']}/input/{$dashboard['api']}/{$dashboard['file_name']}");

    $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
    $endpoint = "https://www.googleapis.com/upload/storage/v1/b/$bucket/o?uploadType=resumable&predefinedAcl=publicRead&name=$file_name";
    $extras = array(array(CURLOPT_HEADER, 1));
    $payload = json_encode(['cacheControl' => 'public, max-age=0, no-transform']);

    //Cloud session URL
    $resumable_session_url = set_curl_adwords($headers, $endpoint, $payload, 'POST', $extras);

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

        //return array('error', '$match' . json_encode($match));
        syslog(LOG_DEBUG, 'match : ' . json_encode($match));

        return $match[0][0];
    }
}

// SET CURL - UPLOAD REPORT GOOGLE CLOUD
function write_data_gstorage($resumable_session_url, $csv_string)
{

    $headers = array('Content-Length: ' . strlen($csv_string));
    $endpoint = $resumable_session_url;
    $payload = $csv_string;

    $response = set_curl_adwords($headers, $endpoint, $payload, 'PUT', null);

    return $response;

}

function read_n_write_data_gstorage($dashboard, $csv_string, $access_token, $bucket)
{

    $file_name = urlencode (  "{$dashboard['dashboard_name']}/input/{$dashboard['api']}/{$dashboard['file_name']}");

    $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
    $endpoint = "https://storage.googleapis.com/$bucket/o/$file_name";

    $response = set_curl_adwords($headers, $endpoint, null, 'GET', null);
    echo '<pre>';
    print $endpoint;
    print $headers;
    print json_encode($response);

    $headers = array('Content-Length: ' . strlen($response.$csv_string));

    $payload = $response.$csv_string;
    $response = set_curl_adwords($headers, $endpoint, $payload, 'PUT', null);

    return $response;

}

//  Get service access token - Function that returns an access token either from db as is not expired yet or straight from the api request
function get_access_token($cloud_storage_data_decoded)
{

    //If the access token has expired
    if (get_http_response_code('https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . $cloud_storage_data_decoded->access_token) != "200") {

        $access_token = get_service_account_access_token($cloud_storage_data_decoded->client, $cloud_storage_data_decoded->scope, $cloud_storage_data_decoded->key);

        if ($access_token) {

            return $access_token;

        } else {

            return false;

        }

        //If the access token has NOT expired
    } else {

        $access_token = $cloud_storage_data_decoded->access_token;

    }

    return $access_token;
}

//  Get service account access token - Function that returns an access token to make calls to google cloud storage
function get_service_account_access_token($client, $scope, $key)
{

    $iat = time();
    $endpoint = "https://www.googleapis.com/oauth2/v4/token";

    //Sign JWT
    $header = array('typ' => 'JWT', 'alg' => 'RS256');
    $jwt_data = array('iss' => $client, 'aud' => $endpoint, 'scope' => $scope, 'exp' => $iat + 3600, 'iat' => $iat);
    $signing_input = base64_url_encode(json_encode($header)) . '.' . base64_url_encode(json_encode($jwt_data));
    openssl_sign($signing_input, $signature, $key, 'SHA256');

    //Request to get the access token linked to a service account
    $jwt = $signing_input . '.' . base64_url_encode($signature);
    $data = array("grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer", "assertion" => $jwt);

    $headers = array('Content-Type: application/x-www-form-urlencoded');
    $payload = http_build_query($data);

    //Access token
    $response = set_curl_adwords($headers, $endpoint, $payload, 'POST', null);
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



//  SET CURL - CREATE AND UPDATE CSV FILE
function create_csv_file($csv_string, $extraction, $storage_data)
{
    $access_token = get_access_token(json_decode($storage_data));


    if ($access_token) {

        $resumable_session_url = get_google_storage_session_url($extraction, json_decode($storage_data)->bucket, $access_token);

        if (!is_array($resumable_session_url)) {

            $report_metadata_latest = upload_report_to_google_storage($resumable_session_url, $csv_string);

            if (!is_array($report_metadata_latest)) {

                return true;

            } else {

                return false;
            }

        } else {
            syslog(LOG_DEBUG, 'Report metadata has not been updated to the Data Base.' );
            return array('error', 'Report metadata has not been updated to the Data Base.');
        }

    } else {
        syslog(LOG_DEBUG, 'access token not found' );
        return array('error', 'access token not found');
    }
}

//  SET CURL - GOOGLE CLOUD SESSION URL
function get_google_storage_session_url($extraction, $bucket, $access_token)
{
    //$file_name = urlencode (  "{$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");
    $file_name = urlencode (  $extraction);

    $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
    $endpoint = "https://www.googleapis.com/upload/storage/v1/b/$bucket/o?uploadType=resumable&predefinedAcl=publicRead&name=$file_name";
    $extras = array(array(CURLOPT_HEADER, 1));
    $payload = json_encode(['cacheControl' => 'public, max-age=0, no-transform']);

    syslog(LOG_DEBUG, 'cloud storage '.$endpoint );

    //Cloud session URL
    $resumable_session_url = set_curl_adwords($headers, $endpoint, $payload, 'POST', $extras);

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

    $response = set_curl_adwords($headers, $endpoint, $payload, 'PUT', null);

    return $response;

}



// return  if values exists
function return_safe($var , $str) {
    if(isset($var)) {
        return $str;
    } else {
        return null;
    }
}

// status log file
function status_log($data) {
    $file_path = "gs://api-jobs-files/status-historical-dcm-".date('Y-m-d').".txt";
    if (file_exists ($file_path) ) {
        $historic_data = file_get_contents($file_path);
    } else {
        $historic_data = '';
    }
    $new_line = date('Y-m-d h:i:s')." ".$data."\n";
    file_put_contents($file_path, $new_line.$historic_data);
}