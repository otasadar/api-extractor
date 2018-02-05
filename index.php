<?php

date_default_timezone_set('Asia/Dubai');

echo '<h1><a href="/run"> -Run Task  </a></h1>';
echo '<br/>';
echo '<h1><a href="/run-tasks"> Local Test </a></h1>';
echo '<br/>';
echo '<h1><a href="/edit"> Edit Config </a></h1>';


if (file_exists ("gs://api-jobs-files/status-historical-dcm-".date('Y-m-d').".txt") ) {
    $historic_data = file_get_contents("gs://api-jobs-files/status-historical-dcm-".date('Y-m-d').".txt");

    echo '<h1> Log file from:'.date('Y-m-d').'</h1>';
    echo '<pre>';
    echo $historic_data;
    echo '</pre>';

}

/*
function status_log($data) {

    $file_path = "gs://api-jobs-files/status-historical-dcm-".date('Y-m-d').".txt";
    if (file_exists ($file_path) ) {
        $historic_data = file_get_contents($file_path);
    } else {
        $historic_data = '';
    }
    $new_line = date('Y-m-d h:i')." ".$data."\n";
    file_put_contents($file_path, $new_line.$historic_data);
    echo $historic_data;
    echo $file_path;
}
*/