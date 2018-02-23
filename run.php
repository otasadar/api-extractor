<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

ini_set('display_errors', 1);
$datetime = new DateTime();
$today = $datetime->format('d-m-Y');
$bucket = "annalect-dashboarding";
$default_config_path = "gs://$bucket/config/latest.php";


if (isset($_POST['code'])){

    $code = $_POST['code'];
    $code = str_replace("<?php", '', $code);

    if (isset($_GET['config'])){
        file_put_contents("gs://$bucket/config/".$_GET['config'], $code);
    } else {
        file_put_contents($default_config_path, $code);
        file_put_contents("gs://$bucket/config/backup-$today.php", $code);
    }

} else {
    $code = file_get_contents($default_config_path);
}

eval($code);


foreach ($extractions['items'] as $key => $extraction) {
    $current = $key + 1;
    $extraction['global'] = $extractions['global'] ;
    $extraction['global']['items_counter'] = count($extractions['items']);
    $task_name = $extraction['api']."-".$extraction['task_name']."-".rand();
    $task = new PushTask('/run-tasks?id='.$extraction['api']."-".$extraction['task_name'], ['extraction' => $extraction, 'extraction_id' => $key], ['name' => $task_name]);
    $task_name = $task->add($extractions['global']['queue']);

    echo "running task :$current of ".count($extractions['items'])." <br/>";
}

echo '<p><a href="https://docs.google.com/spreadsheets/d/'.$extractions['global']['google_sheet']['sheet_id'].'/edit#gid=0">Real Time log</a> </p>';

