<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;
require_once __DIR__ . '/config.php';

foreach ($extractions as $key => $extraction) {
    $current = $key + 1;
    echo "running task $current of ".count($extractions)." <br/>";
    $task = new PushTask('/run-tasks', ['extraction' => $extraction, 'extraction_id' => $key]);
    //$task = new PushTask('/test', ['extraction' => $extraction]);
    $task_name = $task->add('api-extractor-queue');


}