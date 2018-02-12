<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config-h.php';


foreach ($extractions['items'] as $key => $extraction) {
    $current = $key + 1;
    $extraction['extraction_id'] = $key;
    $extraction['global'] = $extractions['global'] ;

    switch ($extraction['api']) {

        case "dcm":

            $extraction['json_request'] = json_decode($extraction['json_request']);
            $extraction['json_request']->schedule->expirationDate = $extraction['global']['dcm']['today'];
            $extraction['json_request']->schedule->startDate = $extraction['global']['dcm']['today'];
            $extraction['json_request'] = json_encode($extraction['json_request']);
            //$extraction['profileIds_validated'] = dcm_get_profilesIds($extraction);
            $extraction['file_name_tpl'] = $extraction['file_name'];

            switch ($extraction['report_type']) {

                case "STANDARD":

                    foreach ($extraction['profileIds'] as $key => $profileId) {

                        /*
                        if (!in_array($profileId, $extraction['profileIds_validated'])) {
                            syslog(LOG_DEBUG, 'profileId not found : ' . $profileId);
                            status_log("DCM {$extraction['report_type']} ERROR profileId not found: $profileId");
                            continue;
                        }
                        */

                        $extraction['current_profileId'] = $profileId;

                        $extraction['file_name'] = str_replace('{profileId}', $extraction['current_profileId'], $extraction['file_name_tpl'] );

                        $task_name = $extraction['api']."-".$extraction['task_name']."-profileID-$profileId-".rand();
                        $task = new PushTask('/run-tasks', ['extraction' => $extraction, 'extraction_id' => $key], ['name' => $task_name]);
                        $task_name = $task->add('api-extractor-staging-2');
                        echo "running task :$current of ".count($extraction['profileIds'])." <br/>";

                    }


                    break;

                default:
                    syslog(LOG_DEBUG, 'Report ID: ' . $extraction['extraction_id'] . ' fail, not report type provided');
                    break;
            }


            break;

        default:
            syslog(LOG_DEBUG, 'Not API Name provided ' . $_SERVER['CURRENT_VERSION_ID ']);
            return array('error', "api not provided to extraction  :" . $extraction['extraction_name']);
            break;
    }

}





