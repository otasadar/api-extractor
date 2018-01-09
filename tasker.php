<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

require_once __DIR__ . '/config.php';

$project = $_POST['project'];

$csv_string_account = '';
$skip_headers = 'false';
$project['access_token'] = update_access_token($client_id, $client_secret, $project['refresh_token']);

foreach ($project['accounts'] as $key => $account) {
    switch ($project['api']) {
        case "adwords":
            $account_data = set_adwords_request(
                $account,
                $project['report'],
                $project['metrics'],
                $project['date'],
                $project['access_token'],
                $developer_token,
                $skip_headers
            );
            $skip_headers = 'true';
            $csv_string_account .= $account_data ? $account_data : '';
            break;
        case "dcm":
            $account_data = set_adwords_request(
                $account,
                $project['report'],
                $project['metrics'],
                $project['date'],
                $project['access_token'],
                $developer_token,
                $skip_headers
            );
            $skip_headers = 'true';
            $csv_string_account .= $account_data ? $account_data : '';
            break;
        default:
            return array('error', "api not provided to project :".$project['project']);
    }

}

create_csv_file($csv_string_account, $project['project'], $storage_data);


/*
 * SET_APIS - INVOKE API FUNCTION TO LOAD THE ACCOUNTS LISTS
 */

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

        return false;
    }
}


/*
 * SET CURL - HELPER METHOD THAT ISSUES A CURL REQUEST
 */

function set_curl($headers, $endpoint, $payload, $type, $extras)
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

    if ($response === false || $info['http_code'] != 200) {

        curl_close($curl);
        return false;

    } else {
        return handle_adwords_api_response($response);
    }
}


/*
 * SET CURL - CREATE AND UPDATE CSV FILE
 */


function create_csv_file($csv_string, $name, $storage_data)
{
    $access_token = get_access_token(json_decode($storage_data));

    if ($access_token) {

        $resumable_session_url = get_google_cloud_session_url($name, json_decode($storage_data)->bucket, $access_token);

        if (!is_array($resumable_session_url)) {

            $report_metadata_latest = upload_report_to_google_cloud($resumable_session_url, $csv_string);

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


/*
 * SET CURL - GOOGLE CLOUD SESSION URL
 */

function get_google_cloud_session_url($name, $bucket, $access_token)
{

    $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
    $endpoint = 'https://www.googleapis.com/upload/storage/v1/b/' . $bucket . '/o?uploadType=resumable&predefinedAcl=publicRead&name=' . $name . '.csv';
    $extras = array(array(CURLOPT_HEADER, 1));
    $payload = json_encode(['cacheControl' => 'public, max-age=0, no-transform']);

    //Cloud session URL
    $resumable_session_url = set_curl($headers, $endpoint, $payload, 'POST', $extras);

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


/*
 * SET CURL - UPLOAD REPORT GOOGLE CLOUD
 */

function upload_report_to_google_cloud($resumable_session_url, $csv_string)
{

    $headers = array('Content-Length: ' . strlen($csv_string));
    $endpoint = $resumable_session_url;
    $payload = $csv_string;

    $response = set_curl($headers, $endpoint, $payload, 'PUT', null);

    return $response;

}


/*
 * Get service access token - Function that returns an access token either from db as is not expired yet or straight from the api request
 */

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


/*
 * Get service account access token - Function that returns an access token to make calls to google cloud storage
 */
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
    $response = set_curl($headers, $endpoint, $payload, 'POST', null);
    $access_token = json_decode($response)->access_token;

    return $access_token;

}

/*
 * GET HTTP RESPONSE CODE - HELPER METHOD TO GET CODE FROM A GET REQUEST
 */

function get_http_response_code($endpoint)
{
    $headers = get_headers($endpoint);
    return substr($headers[0], 9, 3);
}

/*
 * BASE 64 URL ENCODE - HELPER METHOD THAT ENCODES STRING TO BASE64
 */

function base64_url_encode($input)
{
    return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
}



////////////////////////

// ADWORDS


/*
 * Google Adwords API call (Response handler)
 */

function handle_adwords_api_response($api_response)
{
    if (is_array($api_response)) {

        if ($api_response[0] === 'error') {

            $error_reason = '';
            $error_reason_xml = $api_response[1];
            $error_reason_array = explode('</type>', $error_reason_xml);

            foreach (array_slice($error_reason_array, 0, sizeof(explode('</type>', $error_reason_xml)) - 1) as $item) {
                $error_reason = $error_reason === '' ? explode('Error.', $item)[1] : $error_reason . ', ' . explode('Error.', $item)[1];
            }

            return false;
        }

    } else {

        return $api_response;
    }
}

/*
 * Google Adwords API call (HTTP API request and AND conditions)
 */

function set_adwords_request($account, $report, $metrics, $date, $access_token, $developer_token, $skip_headers)
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
    $payload = '__fmt=CSV&__rdquery=' . ' SELECT ' . $metrics . ' FROM ' . $report . ' ' . 'DURING ' . $date;

    //CURL request
    $curl_response = set_curl($headers, $endpoint, $payload, 'POST', null);

    //Return API data
    if ($curl_response) {

        return $curl_response;

    } else {

        return false;
    }
}
