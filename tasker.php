<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

require_once __DIR__ . '/config.php';
$extraction = $_POST['extraction'];
$extraction['extraction_id'] = $_POST['extraction_id'];
$extraction['csv_output'] = '---';
$extraction['file_name_tpl'] = $extraction['file_name'];
$csv_output = '';
$skip_headers = 'false';


switch ($extraction['api_type']) {
    case "google":
        $extraction['access_token'] = update_access_token($client_id, $client_secret, $extraction['refresh_token']);
        break;

    case "facebook":
        break;

    default:
        return array('error', "api not provided to extraction  :".$extraction['extraction_name']);
        break;
}

switch ($extraction['api']) {
    case "adwords":
        foreach ($extraction['accounts'] as $key => $account) {
            $account_data = set_adwords_request(
                $account,
                $extraction['report'],
                $extraction['metrics'],
                $extraction['date'],
                $extraction['access_token'],
                $developer_token,
                $skip_headers
            );
            $skip_headers = 'true';
            $csv_output .= $account_data ? $account_data : '';
        }
        create_csv_file($csv_output, $extraction, $storage_data);
        break;

    case "dcm":
        // todo check when profileID not exists, check null returns
        // todo create a status of each request and send by email
        // todo floodlight custom vars
        // todo move this api switch to task for mutliples task, require open csv files
        // todo create super object from config

        $extraction['json_request'] = json_decode($extraction['json_request']);
        $extraction['json_request']->schedule->expirationDate = $dcm_today;
        $extraction['json_request']->schedule->startDate = $dcm_today;
        $extraction['json_request'] = json_encode($extraction['json_request']);


        $profileIdsValidator = dcm_get_profilesIds($extraction);

        foreach ($extraction['profileIds'] as $key => $profileId) {
            if (!in_array($profileId, $profileIdsValidator)){
                syslog(LOG_DEBUG, 'profileId not found : '.$profileId);
                continue;
            }
            //syslog(LOG_DEBUG, 'init task : '.json_encode($extraction));

            $i = 0;
            switch ($extraction['report_type']) {
                case "STANDARD":
                    $extraction['csv_output'] = dcm_start ($extraction, $profileId);
                    $extraction['csv_output'] = dcm_headers_cleaner ($extraction['csv_output'], 9, true);
                    $extraction['file_name'] = str_replace('{profileId}', $profileId, $extraction['file_name_tpl']);
                    create_csv_file($extraction['csv_output'], $extraction, $storage_data);
                    syslog(LOG_DEBUG, "Saving CSV to : {$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");

                    break;
                case "FLOODLIGHT":
                    $floodlightConfigIdsValidator = dcm_check_floodlightConfigIds($profileId, $extraction) ;
                    foreach ($extraction['floodlightConfigIds'][$profileId] as $key => $floodlightConfigId) {
                        if (!in_array($floodlightConfigId, $floodlightConfigIdsValidator)){
                            syslog(LOG_DEBUG, 'floodlightConfigId not found : '.$floodlightConfigId);
                            continue;
                        }
                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        $extraction['json_request']->floodlightCriteria->floodlightConfigId->value = $floodlightConfigId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);

                        $extraction['csv_output'] .= dcm_start ($extraction, $profileId);
                        if ($i === 0) {
                            $extraction['csv_output'] = dcm_headers_cleaner ($extraction['csv_output'], 9, true);
                        } else {
                            $extraction['csv_output'] = dcm_headers_cleaner ($extraction['csv_output'], 10, true);
                        }
                        $i++;

                        $extraction['file_name'] = str_replace('{profileId}', $profileId, $extraction['file_name_tpl']);
                        create_csv_file($extraction['csv_output'], $extraction, $storage_data);
                        syslog(LOG_DEBUG, "Saving CSV to : {$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");

                    }
                    break;
                case "CROSS_DIMENSION_REACH":
                    $advertiserIdsValidator = dcm_check_advertiserIds($profileId, $extraction) ;


                    foreach ($extraction['advertiserIds'][$profileId] as $key => $advertiserId) {
                        if (!in_array($advertiserId, $advertiserIdsValidator)){
                            syslog(LOG_DEBUG, '$advertiserId not found : '.$advertiserId);
                            continue;
                        }
                        $extraction['json_request'] = json_decode($extraction['json_request']);
                        $extraction['json_request']->crossDimensionReachCriteria->dimensionFilters[0]->id  = $advertiserId;
                        $extraction['json_request'] = json_encode($extraction['json_request']);

                        $extraction['csv_output'] = dcm_start ($extraction, $profileId);
                        $extraction['csv_output'] = dcm_headers_cleaner ($extraction['csv_output'], 10, true);

                        $current_advertiserId = $advertiserId;
                        $extraction['file_name'] = str_replace('{advertiserId}', $advertiserId, $extraction['file_name_tpl']);

                        create_csv_file($extraction['csv_output'], $extraction, $storage_data);
                        syslog(LOG_DEBUG, "Saving CSV to : {$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");


                    }
                    break;
                default:
                    syslog(LOG_DEBUG, 'Report ID: '.$extraction['extraction_id'].' fail, not report type provided');
                    break;
            }
        }

        break;

    default:
        return array('error', "api not provided to extraction  :".$extraction['extraction_name']);
        break;
}








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

    if ($response === false || $info['http_code'] != 200) {

        curl_close($curl);
        return false;

    } else {
        return handle_adwords_api_response($response);
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

    curl_setopt($curl, CURLOPT_TIMEOUT, 60);
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

    if ($response === false || $info['http_code'] != 200) {
        syslog(LOG_DEBUG, "error curl :". $info['http_code'] );
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
    $file_name = urlencode (  "{$extraction['extraction_name']}/input/{$extraction['api']}/{$extraction['file_name']}");

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

function dcm_start ($extraction, $profileId) {
    $extraction['profileId'] = $profileId;
    $api_response = dcm_report_setup($profileId, $extraction);
    $api_response = dcm_run_report($api_response, $extraction);
    $api_response = ask_DCM_data_until_status_available ($api_response, $extraction);
    //$api_response = dcm_headers_cleaner ($api_response, $key, $profileId);
    return $api_response;
}

// DCM Get all profiles ID
function dcm_get_profilesIds($extraction)
{

    $headers = array("Authorization: Bearer ".$extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode( $curl_response , true);

    $profileIdsValidated= [];
    foreach ($curl_response['items'] as $key => $result) {
        $profileIdsValidated [] = $result['profileId'];
    }
    if (!empty($profileIdsValidated)) {
        syslog(LOG_DEBUG, "dcm_check_profilesIds ".implode(',', $profileIdsValidated));
    }
    return $profileIdsValidated;

}

// DCM Check Floodlight IDs list is valid
function dcm_check_floodlightConfigIds($profileId, $extraction )
{

    $floodlightConfigIds = $extraction['floodlightConfigIds'][$profileId];
    $floodlightConfigIds = "?ids=".implode($floodlightConfigIds, '&ids=');
    $headers = array("Authorization: Bearer ".$extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/floodlightConfigurations$floodlightConfigIds";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode( $curl_response , true);

    $floodlightConfigurations = [];
    foreach ($curl_response['floodlightConfigurations'] as $key => $result) {
        $floodlightConfigurations [] = $result['id'];
    }
    if (!empty($floodlightConfigurations)) {
        syslog(LOG_DEBUG, "dcm_check_floodlightConfigIds ".implode(',', $floodlightConfigurations) );
    }

    return $floodlightConfigurations;

}

// DCM Check advertiserIds list is valid
function dcm_check_advertiserIds($profileId, $extraction)
{

    $advertiserIds = $extraction['advertiserIds'][$profileId];
    $advertiserIds = "?ids=".implode($advertiserIds, '&ids=');
    $headers = array("Authorization: Bearer ".$extraction['access_token'], 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/advertisers$advertiserIds";

    $curl_response = set_curl($headers, $endpoint, null, 'GET', null);
    $curl_response = json_decode( $curl_response , true);

    $advertiserIdsValidated = [];
    foreach ($curl_response['advertisers'] as $key => $result) {
        $advertiserIdsValidated [] = $result['id'];
    }
    if (!empty($advertiserIdsValidated)) {
        syslog(LOG_DEBUG, "dcm_check_advertiserIds ".implode(',', $advertiserIdsValidated) );
    }

    return $advertiserIdsValidated;

}

// DCM request 1 - Setup report and get report id
function dcm_report_setup($profileId, $extraction)
{
    // First request to get DCM Report ID
    $headers = array('Content-type: application/json');
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports?access_token=".$extraction['access_token'];
    syslog(LOG_DEBUG, "endpoint ".$endpoint );
    syslog(LOG_DEBUG, "json_request ".$extraction['json_request'] );

    $curl_response = set_curl($headers, $endpoint, $extraction['json_request'], 'POST', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "profileId ".$profileId );
    //
    syslog(LOG_DEBUG, "dcm_report_setup ".json_encode($curl_response) );

    return $curl_response;
}

// DCM request 2 - Run report for get URL
function dcm_run_report($api_response, $extraction)
{

    $reportId = $api_response->id;
    $profileId = $api_response->ownerProfileId;
    $access_token = $extraction['access_token'];

    // Second request to get DCM report CSV status & final URL
    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports/$reportId/run?access_token=$access_token";
    $curl_response = set_curl(null, $endpoint, null, 'POST', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "reportId ".$reportId );
    //syslog(LOG_DEBUG, "profileId ".$profileId );
    syslog(LOG_DEBUG, "dcm_run_report ".json_encode($curl_response) );

    return $curl_response;
}

// DCM request 3 - Check if media report file is generated
function dcm_get_report_url($api_response, $extraction)
{


    $profileId = $extraction['profileId'];
    $reportId = $api_response->reportId;
    $fileId = $api_response->id;
    $access_token = $extraction['access_token'];

    // Second request to get DCM report CSV status & final URL

    $endpoint = "https://www.googleapis.com/dfareporting/v3.0/userprofiles/$profileId/reports/$reportId/files/$fileId?access_token=$access_token";
    $curl_response = set_curl(null, $endpoint, null, 'GET', null);
    $curl_response = json_decode($curl_response);

    //syslog(LOG_DEBUG, "reportId ".$reportId );
    //syslog(LOG_DEBUG, "profileId ".$profileId );
    //syslog(LOG_DEBUG, "fileId ".$fileId );
    syslog(LOG_DEBUG, "dcm_get_report_url ".json_encode($curl_response) );

    return $curl_response;
}

// DCM request 4 - Get CSV content by URL
function dcm_get_report_url_content($api_response, $extraction)
{
    $url = $api_response->urls->apiUrl;
    $status = $api_response->status;
    $access_token = $extraction['access_token'];

    syslog(LOG_DEBUG, "url ".$url );
    syslog(LOG_DEBUG, "status ".$status );

    $headers = array("Authorization: Bearer $access_token");
    $endpoint = "$url";
    $curl_response = set_curl($headers, $endpoint, null, 'GET');
    syslog(LOG_DEBUG, "dcm_get_report_url_content: ".strlen($curl_response) );

    return $curl_response; // check if is a CSV data or JSON
}

// DCM recursive functions for get report data
function ask_DCM_data_until_status_available ($api_response, $extraction) {


    $api_response = dcm_get_report_url($api_response, $extraction);

    if ($api_response->status === "REPORT_AVAILABLE") {
        $api_response2 = dcm_get_report_url_content($api_response, $extraction);
        return $api_response2;

    } else {

        // add an lineal delay for avoid too many request per hour

        if (!isset($extraction['queueDelay'])) {
            $extraction['queueDelay'] = 5;
        }
        else {
            $extraction['queueDelay'] = $extraction['queueDelay'] * 2;
        }

        if  ($extraction['queueDelay'] < $extraction['max_execution_sec']) {

            syslog(LOG_DEBUG, "queueDelay".$extraction['queueDelay'] );
            sleep($extraction['queueDelay']);
            return ask_DCM_data_until_status_available($api_response, $extraction);

        } else {
            die;
        }

    }
}

function dcm_headers_cleaner ($csv_data, $remove_header_lines, $remove_last_line) {
    $csv_data = explode("\n", $csv_data);

    if (isset($remove_header_lines)){
        for ($i = 0; $i < $remove_header_lines; $i++) {
            syslog(LOG_DEBUG, "removed line".$csv_data[$i]);
            unset($csv_data[$i]);
        }
    }

    if (isset($remove_last_line)){
        syslog(LOG_DEBUG, "removed last line".end($csv_data));
        array_pop($csv_data);
        syslog(LOG_DEBUG, "removed last line2".end($csv_data));
        array_pop($csv_data);
    }



    /*
     * $i= 0;
    foreach ($csv_data as $key => $line) {
        if ($i === 0 ) {
            $csv_data[$key] = "ProfileId,".$line;
        }
        else {
            $csv_data[$key] = "123456,".$line;
        }
        $i++;
    }
    */

    return implode("\n", $csv_data);
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
