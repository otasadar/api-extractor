<?php

ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
date_default_timezone_set('Asia/Dubai');

$datetime = new DateTime();
$today = $datetime->format('d-m-Y');

require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();

if (strpos($_SERVER['HTTP_HOST'], 'staging') !== false)  {
    $bucket = "api-extractor-staging";
} else {
    $bucket = "annalect-dashboarding";
}

$config_global = "gs://$bucket/config/config-global.php";

if (file_exists ($config_global)) {
    $config_global = file_get_contents($config_global);
    $config_global = str_replace("<?php", '', $config_global);
} else {
    echo "ERROR : Global Config file not exists";
    die;
}



if (isset($_POST['code'])) {

    $code = $_POST['code'];
    $code = str_replace("<?php", '', $code);

    file_put_contents("gs://$bucket/config/" . $_POST['config'], $code);

} else if (isset($_GET['config'])) {

    if (file_exists ("gs://$bucket/config/" . $_GET['config'])) {
        $code = file_get_contents("gs://$bucket/config/" . $_GET['config']);
    } else {
        echo "ERROR : Config file not exists";
        die;
    }

} else {
    echo "ERROR : Config content not exists or not provided";
    die;
}

if (!isset($_POST['onlySave'])) {
    eval($config_global.$code);

    if (!isset($extractions['items']))  {
        echo "Saved config file, but there is no extractions to process";
        die;
    }


    $i = 0;

    $extractions = $helpers->init_google_sheet($extractions);
    $helpers->sheets_extraction_to_log_files($extractions) ;

    foreach ($extractions['items'] as $key => $extraction) {
        $current = $key + 1;
        $extraction['global'] = $extractions['global'] ;
        $extraction['global']['items_counter'] = count($extractions['items']);
        $extraction['timestamp'] = $datetime->format('d-m-Y-H-i');
        $extraction['task_name'] = $extraction['api']."-".$extraction['extraction_group']."-".$extraction['extraction_name'];
        $extraction['extraction_id'] = rand();

        $task_name = $extraction['task_name']."-".$extraction['extraction_id'];
        $task = new PushTask('/run-tasks-'.$extraction['task_name'].'-'.$extraction['extraction_id'], ['extraction' => $extraction], ['name' => $task_name]);
        $task->add($extractions['global']['queue']);
        $i++;
        echo "running task $i - accountsIds:".count($extraction['accountsData'])." - $task_name <br/>";

    }

    echo "\n";
    echo "queue: {$extractions['global']['queue']}\n";
    echo "task_name: $task_name \n";

} else {
    echo "File saved \n";
}

