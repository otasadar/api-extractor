<?php

$bucket = "annalect-dashboarding";


ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;
$datetime = new DateTime();
$today = $datetime->format('d-m-Y');

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
    file_put_contents("gs://$bucket/config/backup-$today.php", $code);

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



eval($config_global.$code);


foreach ($extractions['items'] as $key => $extraction) {
    $current = $key + 1;
    $extraction['global'] = $extractions['global'];
    $extraction['global']['items_counter'] = count($extractions['items']);
    $task_name = $extraction['api'] . "-" . $extraction['task_name'] . "-" . rand();
    $task = new PushTask('/run-tasks', ['extraction' => $extraction, 'extraction_id' => $key], ['name' => $task_name]);
    $task_name = $task->add($extractions['global']['queue']);

    echo "running task :$current of " . count($extractions['items']) . " <br/>";
}

echo '<p><a href="https://docs.google.com/spreadsheets/d/' . $extractions['global']['google_sheet']['sheet_id'] . '/edit#gid=0">Real Time log</a> </p>';

