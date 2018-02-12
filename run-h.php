<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/config-h.php';


foreach ($extractions['items'] as $key => $extraction) {
    $current = $key + 1;
    $extraction['global'] = $extractions['global'] ;
    $task_name = $extraction['api']."-".$extraction['task_name']."-".rand();
    $task = new PushTask('/run-tasks', ['extraction' => $extraction, 'extraction_id' => $key], ['name' => $task_name]);
    $task_name = $task->add('api-extractor-staging');

    echo "running task :$current of ".count($extractions['items'])." <br/>";
}

