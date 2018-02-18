<?php
/*
ini_set('display_errors', 1);
$datetime = new DateTime();
$today = $datetime->format('d-m-Y');

if (isset($_POST['code'])){

    if (isset($_GET['config'])){
        file_put_contents("gs://annalect-dashboarding/config/".$_GET['config'], $code);
    } else {
        file_put_contents("gs://annalect-dashboarding/config/latest.php", $code);
        file_put_contents("gs://annalect-dashboarding/config/backup-$today.php", $code);
    }
	$code = $_POST['code'];
	$code = str_replace("<?php", '', $code);

	echo $code;

}
*/

