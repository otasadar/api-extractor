<?php
/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 1/24/2018
 * Time: 12:52 PM
 */

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');
$datetime = new DateTime();
$today = $datetime->format('Y/m/d H:i:s');
/*
date_default_timezone_set('Asia/Dubai');
$datetime = new DateTime();



$old = file_get_contents("gs://annalect-dashboarding/dashboard1/input/dcm/hola2.csv");
file_put_contents("gs://annalect-dashboarding/dashboard1/input/dcm/hola3.txt", $today."/n".$old);

*/
/*
function status_log($data) {
    if (file_exists ("gs://api-jobs-files/status-".date('Y-m-d').".txt") ) {
        $historic_data = file_get_contents("gs://api-jobs-files/status-".date('Y-m-d').".txt");

    } else {
        $historic_data = '';
    }
    $new_line = date('Y-m-d h:i')." ".$data."\n";
    file_put_contents("gs://api-jobs-files/status-".date('Y-m-d').".txt", $new_line.$historic_data);
}

status_log('hola manola');
*/



$memcache = new Memcache;
$memcache->set('test'.rand(), $today);

var_dump($memcache->getAllKeys());