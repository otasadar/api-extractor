<?php


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

if (isset($_POST['ftp_server'])) {
    $bucket =  $_POST['bucket'];
    $storage_access_token = $_POST['storage_access_token'];
    $ftp_server = $_POST['ftp_server'];
    $ftp_user = $_POST['ftp_user'];
    $ftp_pass = $_POST['ftp_pass'];
    $path = $_POST['path'];
    $new_file = $_POST['new_file'];
    $final_object = $_POST['final_object'];

}
else {
    echo 'error vars';
    die;
}


$command1 = "curl -k -u $ftp_user:$ftp_pass $path --output $new_file.csv.gz";
$command2 = "gzip -d $new_file.csv.gz";
$command3 = "curl -X POST --data-binary @$new_file.csv -H 'Authorization: Bearer $storage_access_token' -H 'Content-Type: text/csv' 'https://www.googleapis.com/upload/storage/v1/b/$bucket/o?uploadType=media&name=$final_object'";
$command4 = "rm $new_file.csv";

$response = sequential_exec($command1 , true );
$response = sequential_exec($command2 , $response );
$response = sequential_exec($command3 , $response );
$response = sequential_exec($command4 , $response );