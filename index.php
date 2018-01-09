<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;
require_once __DIR__ . '/config.php';

foreach ($projects as $key => $project) {

    $task = new PushTask('/tasker.php', ['project' => $project]);
    $task_name = $task->add('api-report-builder');

}