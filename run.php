<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

//require_once __DIR__ . '/config.php';
ini_set('display_errors', 1);
$datetime = new DateTime();
$today = $datetime->format('d-m-Y');
$default_config_path = 'gs://annalect-dashboarding/config/latest.php';
if (isset($_POST['code'])){

    $code = $_POST['code'];
    $code = str_replace("<?php", '', $code);

    if (isset($_GET['config'])){
        file_put_contents("gs://annalect-dashboarding/config/".$_GET['config'], $code);
    } else {
        file_put_contents($default_config_path, $code);
        file_put_contents("gs://annalect-dashboarding/config/backup-$today.php", $code);
    }


} else {
    $code = file_get_contents($default_config_path);
}

eval($code);


foreach ($extractions['items'] as $key => $extraction) {
    $current = $key + 1;
    $extraction['global'] = $extractions['global'] ;
    $task_name = $extraction['api']."-".$extraction['task_name']."-".rand();
    $task = new PushTask('/run-tasks', ['extraction' => $extraction, 'extraction_id' => $key], ['name' => $task_name]);
    $task_name = $task->add('api-extractor-staging');

    echo "running task :$current of ".count($extractions['items'])." <br/>";
}

echo '<p><a href="https://docs.google.com/spreadsheets/d/1oUslYYAHVtqTwqUSHsPXkXH4EDNC-JGiXPQQAiyrQc0/edit#gid=0">Real Time log</a> </p>';

