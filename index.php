<?php

date_default_timezone_set('Asia/Dubai');

echo '<h1><a href="/run-y"> Run Task Yesterday </a></h1>';
echo '<br/>';
echo '<h1><a href="/run-h"> Run Task Historical </a></h1>';
echo '<br/>';
echo '<h1><a href="/edit"> Edit Config </a></h1>';


$file_path = "gs://api-jobs-files/status-stg-".date('Y-m-d').".txt";
if (file_exists ($file_path) ) {
    $historic_data = file_get_contents($file_path);

    echo '<h1> Log file from:'.date('Y-m-d').'</h1>';
    echo '<pre>';
        echo $historic_data;
    echo '</pre>';

}



