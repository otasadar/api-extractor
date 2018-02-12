<?php
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');

echo "v2";
// todo crate a dcm function to storage
// remove set_curl_adwords() from google storage
// todo create super object from config
// todo manage 404 api response
// todo add csv headers in object instead of capture for first line, for avoid null first report
// todo manage FAIL response
// todo add message when timeout
// todo remove profiles id duplicates in config file
// todo modify floodId validation with list

// todo move this api switch to task for mutliples task, require open csv files


use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;


$extraction = $_POST['extraction'];
$extraction['csv_output'] = '';
$extraction['file_name_tpl'] = $extraction['file_name'];
$skip_headers = 'false'; //todo move this variable

status_log("Start Task {$extraction['file_name']}--------------------------------------");
syslog(LOG_DEBUG, 'Extraction:' . json_encode($extraction));


switch ($extraction['api_type']) {
    case "google":
        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $extraction['access_token'] = get_access_token($client_id, $client_secret, $extraction['refresh_token']);
        $now = new DateTime();
        $extraction['access_token_datetime'] = $now->format('Y-m-d H:i:s');
        break;

    case "facebook":
        break;

    default:
        syslog(LOG_DEBUG, 'Not API Type provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_name']);
        break;
}

switch ($extraction['api']) {
    case "adwords":
        foreach ($extraction['accounts'] as $key => $account) {
            status_log("AdWords Start accountId: $account file_name: {$extraction['file_name']}");
            $extraction['current_accountId'] = $account;
            $account_data = set_adwords_request(
                $account,
                $extraction['report'],
                $extraction['metrics'],
                $extraction['startDate'],
                $extraction['endDate'],
                $extraction['access_token'],
                $developer_token,
                $skip_headers,
                $extraction
            );
            $skip_headers = 'true';
            //$extraction['csv_output'] .= $account_data ? $account_data : '';

            // storage
            if (mb_strlen($account_data) > 1) {
                $extraction['csv_output'] .= $account_data;
                status_log("AdWords OK accountId: $account file_name: {$extraction['file_name']} SizeMb: " . mb_strlen($account_data));

            } else {
                syslog(LOG_DEBUG, "AdWords EMPTY accountId: $account file_name: {$extraction['file_name']} SizeMb: " . mb_strlen($account_data));
                syslog(LOG_DEBUG, $account_data);
                status_log("AdWords EMPTY accountId: $account file_name: {$extraction['file_name']} SizeMb: " . mb_strlen($account_data));
            }
        }
        create_csv_file($extraction['csv_output'], $extraction, $storage_data);
        break;

    case "dcm":

        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->schedule->expirationDate = $extraction['global']['dcm']['today'];
        $extraction['json_request']->schedule->startDate = $extraction['global']['dcm']['today'];
        $extraction['json_request'] = json_encode($extraction['json_request']);
        $extraction['profileIds_validated'] = dcm_get_profilesIds($extraction);

        switch ($extraction['report_type']) {

            case "STANDARD":

                /*
                foreach ($extraction['profileIds'] as $key => $profileId) {

                    if (!in_array($profileId, $extraction['profileIds_validated'])) {
                        syslog(LOG_DEBUG, 'profileId not found : ' . $profileId);
                        status_log("DCM {$extraction['report_type']} ERROR profileId not found: $profileId");
                        continue;
                    }

                    $extraction['current_profileId'] = $profileId;
                    $extraction = check_access_token($extraction);
                    $raw_data = dcm_start($extraction, $profileId);
                    $extraction = dcm_get_report_header($raw_data, $extraction, 'Campaign');
                    $raw_data = dcm_headers_cleaner($raw_data, $extraction, 'Campaign', true);
                    $extraction = dcm_preparing_csv_file($raw_data, $extraction);

                }
                */

                $extraction = check_access_token($extraction);
                $extraction['csv_output'] = dcm_start($extraction, $extraction['current_profileId']);
                //$extraction = dcm_get_report_header($raw_data, $extraction, 'Campaign');
                //$raw_data = dcm_headers_cleaner($raw_data, $extraction, 'Campaign', true);
                //$extraction = dcm_preparing_csv_file($raw_data, $extraction);


                break;

            case "FLOODLIGHT":


                $i = 0;

                foreach ($extraction['floodlightConfigIds'] as $profileId => $floodlightConfigIds) {

                    // profileId validation
                    if (!in_array($profileId, $extraction['profileIds_validated'])) {
                        syslog(LOG_DEBUG, 'profileId not found : ' . $profileId);
                        status_log("DCM {$extraction['report_type']} ERROR profileId not found: $profileId");
                        continue;
                    }

                    foreach ($floodlightConfigIds as $floodlightConfigId) {
                        $extraction = check_access_token($extraction);
                        $floodlightConfigIdsValidator = dcm_check_floodlightConfigIds($profileId, $extraction);

                        // floodlightConfigIds validation
                        if (!in_array($floodlightConfigId, $floodlightConfigIdsValidator)) {
                            syslog(LOG_DEBUG, '$floodlightConfigId not found as a valid one : ' . $floodlightConfigId);
                            status_log("DCM {$extraction['report_type']} ERROR advertiserId not found : $floodlightConfigId - profileId: $profileId");
                            continue;
                        }

                        $extraction['current_profileId'] = $profileId;
                        $extraction['current_floodlightConfigId'] = $floodlightConfigId;
                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        $extraction['json_request']->floodlightCriteria->floodlightConfigId->value = $floodlightConfigId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);

                        $raw_data = dcm_start($extraction, $profileId);
                        $extraction = dcm_get_report_header($raw_data, $extraction, 'Campaign');
                        $raw_data = dcm_headers_cleaner($raw_data, $extraction, 'Campaign', true);
                        $extraction = dcm_preparing_csv_file($raw_data, $extraction);

                        $i++;
                    }


                }
                break;

            case "CROSS_DIMENSION_REACH":

                $i = 0;
                foreach ($extraction['advertiserIds'] as $profileId => $advertiserIds) {

                    // profileId validation
                    if (!in_array($profileId, $extraction['profileIds_validated'])) {
                        syslog(LOG_DEBUG, 'profileId not found : ' . $profileId);
                        status_log("DCM {$extraction['report_type']} ERROR profileId not found: $profileId");
                        continue;
                    }

                    foreach ($advertiserIds as $advertiserId) {
                        $extraction = check_access_token($extraction);
                        $advertiserIdsValidator = dcm_check_advertiserIds($profileId, $extraction);

                        // prev validations
                        if (!in_array($advertiserId, $advertiserIdsValidator)) {
                            syslog(LOG_DEBUG, '$advertiserId not found : ' . $advertiserId);
                            status_log("DCM {$extraction['report_type']} ERROR advertiserId not found : $advertiserId - profileId: $profileId");
                            continue;
                        }
                        $extraction['current_profileId'] = $profileId;
                        $extraction['current_advertiserId'] = $advertiserId;
                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        $extraction['json_request']->crossDimensionReachCriteria->dimensionFilters[0]->id = $advertiserId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);

                        // start pull data
                        $raw_data = dcm_start($extraction, $profileId);
                        $extraction = dcm_get_report_header($raw_data, $extraction, 'Campaign');
                        $raw_data = dcm_headers_cleaner($raw_data, $extraction, 'Campaign', true);
                        $extraction = dcm_preparing_csv_file($raw_data, $extraction);

                    }

                }
                break;

            default:
                syslog(LOG_DEBUG, 'Report ID: ' . $extraction['extraction_id'] . ' fail, not report type provided');
                break;
        }

        create_csv_file($extraction);
        $bucket = $extraction['global']['storage_data']['bucket'];
        syslog(LOG_DEBUG, "Saving CSV to bucket : $bucket filename: {$extraction['file_name']}");

        break;

    default:
        syslog(LOG_DEBUG, 'Not API Name provided ' . $_SERVER['CURRENT_VERSION_ID ']);
        return array('error', "api not provided to extraction  :" . $extraction['extraction_name']);
        break;
}


//  SET_APIS - INVOKE API FUNCTION TO LOAD THE ACCOUNTS LISTS
function get_access_token($client_id, $client_secret, $refresh_token)
{

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

function check_access_token($extraction)
{
    $now = new DateTime();
    $start_date = new DateTime($extraction['access_token_datetime']);
    $since_start = $start_date->diff(new DateTime($now->format('Y-m-d H:i:s')));

    $minutes = $since_start->days * 24 * 60;
    $minutes += $since_start->h * 60;
    $minutes += $since_start->i;

    if ($minutes > 50) {
        status_log('access_token');
        syslog(LOG_DEBUG, 'access_token');

        $client_id = $extraction['global']['google']['client_id'];
        $client_secret = $extraction['global']['google']['client_secret'];
        $extraction['access_token'] = get_access_token($client_id, $client_secret, $extraction['refresh_token']);
        $extraction['access_token_datetime'] = $now->format('Y-m-d H:i:s');
    }

    return $extraction;

}

//  SET CURL ADWORDS - HELPER METHOD THAT ISSUES A CURL REQUEST
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
        return handle_adwords_api_response($response, $extraction);
    }
}

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
        $response = set_simple_curl($info['redirect_url']);
        syslog(LOG_DEBUG, "simple curl :" . mb_strlen($response));
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


//  SET CURL - CREATE AND UPDATE CSV FILE
function create_csv_file($extraction)
{

    $csv_string = $extraction['csv_output'];
    $storage_data = $extraction['global']['storage_data'];
    $bucket = $extraction['global']['storage_data']['bucket'];


    $access_token = get_storage_access_token($extraction);


    if ($access_token) {
        $resumable_session_url = get_google_storage_session_url($extraction, $bucket, $access_token);
        if (!is_array($resumable_session_url)) {

            $report_metadata_latest = upload_report_to_google_storage($resumable_session_url, $csv_string);

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
function get_storage_access_token($extraction)
{

    //If the access token has expired
    $gcs_access_token = $extraction['global']['storage_data']['access_token'];
    $gcs_client = $extraction['global']['storage_data']['client'];
    $gcs_scope = $extraction['global']['storage_data']['scope'];
    $gcs_key = $extraction['global']['storage_data']['key'];

    if (get_http_response_code('https://www.googleapis.com/oauth2/v3/tokeninfo?access_token=' . $gcs_access_token) != "200") {
        $gcs_access_token = get_service_account_access_token($gcs_client, $gcs_scope, $gcs_key);
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
function return_safe($var, $str)
{
    if (isset($var)) {
        return $str;
    } else {
        return null;
    }
}

// status log file
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


////////////////////////
// ADWORDS API FUNCTIONS

// Google Adwords API call (Response handler)
function handle_adwords_api_response($api_response, $extraction)
{
    if (is_array($api_response)) {

        if ($api_response[0] === 'error') {

            $error_reason = '';
            $error_reason_xml = $api_response[1];
            $error_reason_array = explode('</type>', $error_reason_xml);

            foreach (array_slice($error_reason_array, 0, sizeof(explode('</type>', $error_reason_xml)) - 1) as $item) {
                $error_reason = $error_reason === '' ? explode('Error.', $item)[1] : $error_reason . ', ' . explode('Error.', $item)[1];
            }

            syslog(LOG_DEBUG, "AdWords API connection Error " . $api_response);
            status_log("AdWords API_Error" . $api_response . " accountId: {$extraction['current_accountId']} file_name: {$extraction['file_name']}");
            return false;
        }

    } else {

        return $api_response;
    }
}

// Google Adwords API call (HTTP API request and AND conditions)
function set_adwords_request($account, $report, $metrics, $startDate, $endDate, $access_token, $developer_token, $skip_headers, $extraction)
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
    //$payload = '__fmt=CSV&__rdquery=' . ' SELECT ' . $metrics . ' FROM ' . $report . ' ' . 'DURING ' . $date;
    $payload = "__fmt=CSV&__rdquery= SELECT $metrics FROM $report DURING $startDate,$endDate";

    //CURL request
    $curl_response = set_curl_adwords($headers, $endpoint, $payload, 'POST', null, $extraction);

    //Return API data
    if ($curl_response) {
        return $curl_response;
    } else {
        return false;
    }
}


////////////////////////
// DCM API FUNCTIONS 

function dcm_start($extraction, $profileId)
{
    $extraction['profileId'] = $profileId;
    $api_response = dcm_report_setup($profileId, $extraction);
    $api_response = dcm_run_report($api_response, $extraction);
    $api_response = ask_DCM_data_until_status_available($api_response, $extraction);
    return $api_response;

    /*
    if (isset($api_response)) {
        $api_response = dcm_run_report($api_response, $extraction);
    }
    else  {
        status_log("DCM {$extraction['report_type']} ERROR run report: {$extraction['report_type']} profileId: $profileId");
        return false;
    }

    if (isset($api_response)) {
        $api_response = ask_DCM_data_until_status_available ($api_response, $extraction);
    }
    else  {
        status_log("DCM  {$extraction['report_type']} ERROR ".
            return_safe($extraction['current_profileId'] , "profileId not found {$extraction['current_profileId']}").
            return_safe($extraction['current_floodlightConfigId'] , "floodlightConfigId not found {$extraction['current_floodlightConfigId']}").
            return_safe($extraction['current_advertiserId'] , "advertiserId not found {$extraction['current_advertiserId']}"));
        return false;
    }

    if (isset($api_response)) {
        return $api_response;
    }
    else  {
        status_log("DCM ERROR {$extraction['report_type']} {$extraction['report_type']}".
            return_safe($extraction['current_profileId'] , "profileId not found {$extraction['current_profileId']}").
            return_safe($extraction['current_floodlightConfigId'] , "floodlightConfigId not found {$extraction['current_floodlightConfigId']}").
            return_safe($extraction['current_advertiserId'] , "advertiserId not found {$extraction['current_advertiserId']}"));
        return false;
    }
    */


}

// DCM Get all profiles ID
function dcm_get_profilesIds($extraction)
{

    $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode($curl_response, true);

    $profileIdsValidated = [];
    foreach ($curl_response['items'] as $key => $result) {
        $profileIdsValidated [] = $result['profileId'];
    }
    if (!empty($profileIdsValidated)) {
        syslog(LOG_DEBUG, "dcm_check_profilesIds " . implode(',', $profileIdsValidated));
    }
    return $profileIdsValidated;

}

// DCM Check Floodlight IDs list is valid
function dcm_check_floodlightConfigIds($profileId, $extraction)
{

    $floodlightConfigIds = $extraction['floodlightConfigIds'][$profileId];
    $floodlightConfigIds = "?ids=" . implode($floodlightConfigIds, '&ids=');
    $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/floodlightConfigurations$floodlightConfigIds";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode($curl_response, true);

    $floodlightConfigIdsValidated = [];
    foreach ($curl_response['floodlightConfigurations'] as $key => $result) {
        $floodlightConfigIdsValidated [] = $result['id'];
    }
    if (!empty($floodlightConfigIdsValidated)) {
        syslog(LOG_DEBUG, "dcm_check_floodlightConfigIds " . implode(',', $floodlightConfigIdsValidated));
    }

    return $floodlightConfigIdsValidated;

}

// DCM Check advertiserIds list is valid
function dcm_check_advertiserIds($profileId, $extraction)
{

    $advertiserIds = $extraction['advertiserIds'][$profileId];
    $advertiserIds = "?ids=" . implode($advertiserIds, '&ids=');
    $headers = array("Authorization: Bearer " . $extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/advertisers$advertiserIds";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode($curl_response, true);

    $advertiserIdsValidated = [];
    foreach ($curl_response['advertisers'] as $key => $result) {
        $advertiserIdsValidated [] = $result['id'];
    }
    if (!empty($advertiserIdsValidated)) {
        syslog(LOG_DEBUG, "dcm_check_advertiserIds " . implode(',', $advertiserIdsValidated));
    }

    return $advertiserIdsValidated;

}

// DCM request 1 - Setup report and get report id
function dcm_report_setup($profileId, $extraction)
{
    // First request to get DCM Report ID
    $headers = array('Content-type: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports?access_token=" . $extraction['access_token'];
    syslog(LOG_DEBUG, "endpoint " . $endpoint);
    syslog(LOG_DEBUG, "json_request " . $extraction['json_request']);

    $curl_response = set_curl($headers, $endpoint, $extraction['json_request'], 'POST', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "profileId ".$profileId );
    //
    syslog(LOG_DEBUG, "dcm_report_setup " . json_encode($curl_response));

    return $curl_response;
}

// DCM request 2 - Run report for get URL
function dcm_run_report($api_response, $extraction)
{

    $reportId = $api_response->id;
    $profileId = $api_response->ownerProfileId;
    $access_token = $extraction['access_token'];
    $headers = array("Authorization: Bearer $access_token", 'Accept: application/json');


    // Second request to get DCM report CSV status & final URL
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports/$reportId/run";
    $curl_response = set_curl($headers, $endpoint, '', 'POST', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "reportId ".$reportId );
    //syslog(LOG_DEBUG, "profileId ".$profileId );
    syslog(LOG_DEBUG, "dcm_run_report " . json_encode($curl_response));

    return $curl_response;
}

// DCM request 3 - Check if media report file is generated
function dcm_get_report_url($api_response, $extraction)
{


    $profileId = $extraction['profileId'];
    $reportId = $api_response->reportId;
    $fileId = $api_response->id;
    $access_token = $extraction['access_token'];
    $headers = array("Authorization: Bearer $access_token ", 'Accept: application/json');


    // Second request to get DCM report CSV status & final URL

    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports/$reportId/files/$fileId";
    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "reportId ".$reportId );
    //syslog(LOG_DEBUG, "profileId ".$profileId );
    //syslog(LOG_DEBUG, "fileId ".$fileId );
    syslog(LOG_DEBUG, "dcm_get_report_url " . json_encode($curl_response));

    return $curl_response;
}

// DCM request 4 - Get CSV content by URL
function dcm_get_report_url_content($api_response, $extraction)
{
    status_log("original CSV : {$extraction['file_name']} ".$api_response->urls->browserUrl);

    $url = $api_response->urls->apiUrl;
    $status = $api_response->status;
    $access_token = $extraction['access_token'];

    syslog(LOG_DEBUG, "url " . $url);
    syslog(LOG_DEBUG, "status " . $status);

    $headers = array("Authorization: Bearer $access_token");
    $endpoint = "$url";
    $curl_response = set_curl($headers, $endpoint, null, 'GET');
    syslog(LOG_DEBUG, "dcm_get_report_url_content: " . strlen($curl_response));

    return $curl_response; // check if is a CSV data or JSON
}


// DCM recursive functions for get report data
function ask_DCM_data_until_status_available($api_response, $extraction)
{
    $extraction = check_access_token($extraction);
    $api_response = dcm_get_report_url($api_response, $extraction);
    /*
    if (!isset($extraction['queueDelay'])) {
        $extraction['queueDelay'] = 5;
    } else {
        $extraction['queueDelay'] = $extraction['queueDelay'] * 2;
    }
    */


    if ($api_response->status === "REPORT_AVAILABLE") {
        $api_response2 = dcm_get_report_url_content($api_response, $extraction);
        return $api_response2;

    } else if ($api_response->status === "PROCESSING") {
        syslog(LOG_DEBUG, "queueDelay:60");
        sleep(60);
        return ask_DCM_data_until_status_available($api_response, $extraction);


        /*
        if ($extraction['queueDelay'] < $extraction['max_execution_sec']) {

            syslog(LOG_DEBUG, "queueDelay" . $extraction['queueDelay']);
            sleep($extraction['queueDelay']);
            return ask_DCM_data_until_status_available($api_response, $extraction);

        } else {
            status_log("DCM {$extraction['report_type']} TIMEOUT" .
                return_safe($extraction['current_profileId'], "profileId: {$extraction['current_profileId']}") .
                return_safe($extraction['current_floodlightConfigId'], "floodlightConfigId: {$extraction['current_floodlightConfigId']}") .
                return_safe($extraction['current_advertiserId'], "advertiserId: {$extraction['current_advertiserId']}"));
            syslog(LOG_DEBUG, "TIMEOUT " . $extraction['max_execution_sec']);
        }
        */

    } else {
        syslog(LOG_DEBUG, "Report ERROR :  " . $api_response->status);
        status_log("DCM {$extraction['report_type']} ERROR:$api_response->status" .
            return_safe($extraction['current_profileId'], " profileId: {$extraction['current_profileId']}") .
            return_safe($extraction['current_floodlightConfigId'], " floodlightConfigId: {$extraction['current_floodlightConfigId']}") .
            return_safe($extraction['current_advertiserId'], " advertiserId: {$extraction['current_advertiserId']}"));
    }
}

function dcm_get_report_header($raw_data, $extraction, $needle)
{

    $rows = explode("\n", $raw_data);

    if (isset($needle)) {
        for ($i = 0; $i < count($rows); $i++) {

            if (strpos($rows[$i], $needle) !== false) {
                if (empty($extraction['csv_output'])) {
                    switch ($extraction['report_type']) {
                        case "STANDARD":
                            $extraction['csv_output'] = "ProfileId," . $rows[$i] . "\n";
                            syslog(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");

                            break;
                        case "FLOODLIGHT":
                            $extraction['csv_output'] = "ProfileId," . $rows[$i] . "\n";
                            syslog(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");
                            break;

                        case "CROSS_DIMENSION_REACH":
                            $extraction['csv_output'] = "AdvertiserId," . $rows[$i] . "\n";
                            syslog(LOG_DEBUG, "Adding header:{$extraction['csv_output']}");
                            break;
                    }
                }

                break;
            }
        }
    }
    return $extraction;

}

function dcm_headers_cleaner($raw_data, $extraction, $needle, $remove_last_line)
{
    if (empty($raw_data)) {
        return $raw_data;
    } else {
        $rows = explode("\n", $raw_data);

        $total_rows = count($rows);

        //remove headers
        if (isset($needle)) {
            for ($i = 0; $i < $total_rows; $i++) {

                if (strpos($rows[$i], $needle) !== false) {
                    syslog(LOG_DEBUG, "founded needle:" . $needle);
                    syslog(LOG_DEBUG, "removed last line" . $rows[$i]);
                    syslog(LOG_DEBUG, "removed space line" . $rows[($i + 1)]);

                    if (empty(trim($rows[($i + 1)]))) {
                        unset($rows[($i + 1)]); //next line is empty
                    }
                    unset($rows[$i]);
                    break;
                } else {
                    syslog(LOG_DEBUG, "removed line" . $rows[$i]);
                    unset($rows[$i]);
                }

            }
        } else {
            syslog(LOG_DEBUG, "needle not FOUND:" . $raw_data);
        }

        // remove footer
        if (isset($remove_last_line)) {
            syslog(LOG_DEBUG, "removed footer line" . end($rows));
            array_pop($rows);
            syslog(LOG_DEBUG, "removed footer line2" . end($rows));
            array_pop($rows);
        }

        //add id to beginning
        foreach ($rows as $key => $line) {
            switch ($extraction['report_type']) {
                case "STANDARD":
                case "FLOODLIGHT":
                    $rows[$key] = $extraction['current_profileId'] . "," . $line;
                    break;

                case "CROSS_DIMENSION_REACH":
                    $rows[$key] = $extraction['current_advertiserId'] . "," . $line;
                    break;
            }
        }

        $filter_data = implode("\n", $rows);
        return $filter_data . "\n";
    }

}

function dcm_preparing_csv_file($raw_data, $extraction)
{
    if (mb_strlen($raw_data) > 1) {
        $extraction['csv_output'] .= $raw_data;
        status_log("DCM {$extraction['report_type']} OK profileId: {$extraction['current_profileId']} Size Mb:" . mb_strlen($raw_data));

    } else {

        syslog(LOG_DEBUG,"DCM {$extraction['report_type']} EMPTY " .
            return_safe($extraction['current_profileId'], "profileId: {$extraction['current_profileId']}") .
            return_safe($extraction['current_floodlightConfigId'], "floodlightConfigId: {$extraction['current_floodlightConfigId']}") .
            return_safe($extraction['current_advertiserId'], "advertiserId: {$extraction['current_advertiserId']}"));
        syslog(LOG_DEBUG, $raw_data);

        status_log("DCM {$extraction['report_type']} EMPTY ".
            return_safe($extraction['current_profileId'], "profileId: {$extraction['current_profileId']}").
            return_safe($extraction['current_floodlightConfigId'], "floodlightConfigId: {$extraction['current_floodlightConfigId']}").
            return_safe($extraction['current_advertiserId'], "advertiserId: {$extraction['current_advertiserId']}"));
    }
    return $extraction;
}

