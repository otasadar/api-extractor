<?php
ini_set('display_errors', 1);
use google\appengine\api\taskqueue\PushTask;
use google\appengine\api\taskqueue\PushQueue;
use google\appengine\api\log\LogService;
require_once __DIR__ . '/config.php';

foreach ($dashboards as $key => $dashboard) {

    $task = new PushTask('/run-tasks', ['dashboard' => $dashboard]);
    $task_name = $task->add('annalect-api-jobs-queue');

}