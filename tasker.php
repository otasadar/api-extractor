<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

require_once __DIR__ . '/config.php';

$dashboard = $_POST['dashboard'];

$csv_output = '';
$skip_headers = 'false';
$dashboard['access_token'] = update_access_token($client_id, $client_secret, $dashboard['refresh_token']);

switch ($dashboard['api']) {
    case "adwords":
        foreach ($dashboard['accounts'] as $key => $account) {
            $account_data = set_adwords_request(
                $account,
                $dashboard['report'],
                $dashboard['metrics'],
                $dashboard['date'],
                $dashboard['access_token'],
                $developer_token,
                $skip_headers
            );
            $skip_headers = 'true';
            $csv_output .= $account_data ? $account_data : '';

        }
        create_csv_file($csv_output, $dashboard, $storage_data);
        break;

    case "dcm":
        foreach ($dashboard['profileIds'] as $key => $profileId) {

            $dcm_report = set_dcm_reportid_request(
                $profileId, $dashboard['json_request'], $dashboard['access_token']
            );
            
            $csv_output .= ask_DCM_data_until_status_available ($dcm_report, $dashboard);
            
        }
        create_csv_file($csv_output, $dashboard, $storage_data);
        break;

    default:
        return array('error', "api not provided to dashboard  :".$dashboard['dashboard_name']);
        break;
}





//  SET_APIS - INVOKE API FUNCTION TO LOAD THE ACCOUNTS LISTS
function update_access_token($client_id, $client_secret, $refresh_token)
{

    //POST request
    $headers = array('Content-type: application/x-www-form-urlencoded');
    $endpoint = 'https://www.googleapis.com/oauth2/v4/token';
    $payload = 'client_id=' . $client_id . '&client_secret=' . $client_secret . '&refresh_token=' . $refresh_token . '&grant_type=refresh_token';
    $access_token = set_curl_adwords($headers, $endpoint, $payload, 'POST', null);

    if ($access_token) {

        return json_decode($access_token)->access_token;

    } else {

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

    if ($response === false || $info['http_code'] != 200) {

        curl_close($curl);
        return false;

    } else {
        return handle_adwords_api_response($response);
    }
}

//  SET CURL GENERAL - HELPER METHOD THAT ISSUES A CURL REQUEST
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
    curl_close($curl);

    if ($response === false || $info['http_code'] != 200) {
        return false;
    } else {
        return $response;
    }
}

//  SET CURL - CREATE AND UPDATE CSV FILE
function create_csv_file($csv_string, $dashboard, $storage_data)
{
    $access_token = get_access_token(json_decode($storage_data));


    if ($access_token) {

        $resumable_session_url = get_google_storage_session_url($dashboard, json_decode($storage_data)->bucket, $access_token);

        if (!is_array($resumable_session_url)) {

            $report_metadata_latest = upload_report_to_google_storage($resumable_session_url, $csv_string);

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
function get_google_storage_session_url($dashboard, $bucket, $access_token)
{

    $headers = array('X-Upload-Content-Type: text/csv', 'Content-Type: application/json; charset=UTF-8', 'Authorization : Bearer ' . $access_token);
    $endpoint = "https://www.googleapis.com/upload/storage/v1/b/$bucket/{$dashboard['dashboard_name']}/input/{$dashboard['api']}/o?uploadType=resumable&predefinedAcl=publicRead&name={$dashboard['file_name']}";
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




////////////////////////
// ADWORDS API FUNCTIONS

// Google Adwords API call (Response handler)
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

// Google Adwords API call (HTTP API request and AND conditions)
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
    $curl_response = set_curl_adwords($headers, $endpoint, $payload, 'POST', null);

    //Return API data
    if ($curl_response) {

        return $curl_response;

    } else {

        return false;
    }
}




////////////////////////
// DCM API FUNCTIONS 

// DCM request 1 - Setup report and get report id
function set_dcm_reportid_request($profileId, $json_request, $access_token)
{

    // First request to get DCM Report ID
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports?access_token=$access_token";
    $curl_response = set_curl(null, $endpoint, null, 'GET',  $json_request);

    // Second request to get DCM report CSV
    $curl_response = json_decode($curl_response);

    //Return API data
    return $curl_response;
}

// DCM request 2 - Check if report status and get report csv URL
function set_dcm_reportcsv_request($api_response, $access_token)
{

    $reportId = $api_response['id'];
    $profileId = $api_response['ownerProfileId'];
    $extras['synchronous'] = true;

    // First request to get DCM Report ID
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports/$reportId/run?access_token=$access_token";
    $curl_response = set_curl(null, $endpoint, null, 'GET');

    // Second request to get DCM report CSV
    $curl_response = json_decode($curl_response);

    //Return API data
    return $curl_response;
}

// DCM request 3 - Get CSV content
function set_dcm_file_request($api_response, $access_token)
{
    $url = $api_response['urls']['apiUrl'];
    $endpoint = "$url&access_token=$access_token";
    $curl_response = set_curl(null, $endpoint, null, 'GET');
    return $curl_response; // check if is a CSV data or JSON
}

// DCM recursive functions for get report data
function ask_DCM_data_until_status_available ($dcm_report, $dashboard) {

    $dcm_report = set_dcm_reportcsv_request(
        $dcm_report, $dashboard['access_token']
    );

    if ($dcm_report['status'] === "REPORT_AVAILABLE") {
        $dcm_report = set_dcm_file_request(
            $dcm_report, $dashboard['access_token']
        );
        return $dcm_report;

    } else {
        // add an exponential delay for avoid too many request
        if (!$dashboard['queueDelay']) {
            $dashboard['queueDelay'] = 5;
        } else {
            $dashboard['queueDelay'] = $dashboard['queueDelay'] *2;
        }
        $dashboard['reportId'] = $dcm_report['reportId'];
        $dashboards[] = $dashboard;
        sleep($dashboard['queueDelay']);
        checkStatus();
    }
}

// DCM errors handle
function handle_dcm_api_response($api_response)
{
    if ($api_response['error']) {
        return array('error', $api_response['code'].'-'.$api_response['message']);
    }
    else if (count($api_response) === 0) {
        return array('error', 'empty response');

    } else {
        return true;
    }

}
