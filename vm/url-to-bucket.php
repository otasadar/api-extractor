<?php

ini_set('display_errors', 1);

if (isset($_POST['url'])) {
    $url= $_POST['url'];
    $url_token=$_POST['url_token'];
    $gcs_token=$_POST['gcs_token'];
    $tmp_file=$_POST['tmp_file'];
    $bucket= $_POST['bucket'];
    $object= $_POST['object'];
    //$object= "{$extraction['extraction_group']}/input/{$extraction['api']}/$tmp_file";
    $api= $_POST['api'];
    $Expires = $_POST['Expires']; // dbm
    $Signature = $_POST['Signature']; // dbm
} else {
    echo "error post";
    die;
}



function sequential_exec($command , $prev_response ) {

    if ($prev_response) {
        exec($command, $output, $return);

        if ($return != 0) {
            echo "ERROR $return : $command";
            //$helpers->gae_log(LOG_DEBUG, 'exec error' . $return);
            return false;

        } else {
            echo "Completed : $command <br>" ;
            //$helpers->gae_log(LOG_DEBUG, 'exec' . json_encode($output));
            var_dump($output);
            return true;
        }
    }
}


switch ($api) {
    case "ds":
        $command1 = "wget -d --header='Authorization: Bearer $url_token' -O $tmp_file '$url'";
        break;
    case "dbm":
        $url = "$url&Expires=$Expires&Signature=$Signature";
        $url = str_replace('#', '%', $url);
        $command1 = "wget -d -O $tmp_file '$url'";
        break;
    default:
        $command1 = "wget -d -O $tmp_file '$url'";
        break;
}

$command2 = "curl -X POST --data-binary @$tmp_file -H 'Authorization: Bearer $gcs_token' -H 'Content-Type: text/csv' 'https://www.googleapis.com/upload/storage/v1/b/$bucket/o?uploadType=media&name=$object'";
$command3 = "rm $tmp_file";

$response = sequential_exec($command1 , true );
$response = sequential_exec($command2 , $response );
$response = sequential_exec($command3 , $response );


?>