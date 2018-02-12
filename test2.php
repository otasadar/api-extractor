<?php
/**
 * Created by PhpStorm.
 * User: Alejandro.Viveros
 * Date: 1/24/2018
 * Time: 12:52 PM
 */

ini_set('display_errors', 1);
date_default_timezone_set('Asia/Dubai');
/*
date_default_timezone_set('Asia/Dubai');
$datetime = new DateTime();

$today = $datetime->format('Y/m/d H:i:s');

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

/**
 * Download a large distant file to a local destination.
 *
 * This method is very memory efficient :-)
 * The file can be huge, PHP doesn't load it in memory.
 *
 * /!\ Warning, the return value is always true, you must use === to test the response type too.
 *
 * @author dalexandre
 * @param string $url
 *    The file to download
 * @param ressource $dest
 *    The local file path or ressource (file handler)
 * @return boolean true or the error message
 */

/*
public static function downloadDistantFile($url, $dest)
{
    $options = array(
        CURLOPT_FILE => is_resource($dest) ? $dest : fopen($dest, 'w'),
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_URL => $url,
        CURLOPT_FAILONERROR => true, // HTTP code > 400 will throw curl error
    );

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $return = curl_exec($ch);

    if ($return === false)
    {
        return curl_error($ch);
    }
    else
    {
        return true;
    }
}
*/



$hash = file_get_contents("gs://annalect-dashboarding/dcm_standard_historical_split_2872652.csv");

$handle = fopen($value,"rb");

while (!feof($handle)) {
    $chunk = fread($handle,1048576);
    // split in lines
    // modify
    // merge
    // combine
    fwrite($fp, $result);
}



$list = explode("\n",$hash);
$fp = fopen("gs://annalect-dashboarding/dcm_copy.csv","ab");
foreach($list as $value){
    if(!empty($value)) {
        $handle = fopen($value,"rb");
        //fwrite($fp,fread($handle,filesize($value)));
        while (!feof($handle)) {
            fwrite($fp,fread($handle,1048576));
        }
        fclose($handle);
        unset($handle);
    }
}
fclose($fp);
echo "ok";



$extraction['report_type'] = 'STANDARD';
$extraction['current_profileId'] = '2872652';




function dcm_headers_cleaner($url_data, $extraction, $needle, $remove_last_line)
{
    if (!empty($url_data)) {

        $handle = fopen($url_data,"rb");
        $fp = fopen("gs://annalect-dashboarding/dcm_copy.csv","ab");
        $pointer = 0;
        while (!feof($handle)) {
            $chunk = fread($handle,1048576);
            // split in lines
            // modify
            // merge
            // combine



            $rows = explode("\n", $chunk);
            $total_rows = count($rows);

            //remove headers
            if (isset($needle) && $pointer === 0) {
                for ($i = 0; $i < $total_rows; $i++) {

                    if (strpos($rows[$i], $needle) !== false) {

                        if (empty(trim($rows[($i + 1)]))) {
                            unset($rows[($i + 1)]); //next line is empty
                        }
                        unset($rows[$i]);
                        break;
                    } else {
                        //syslog(LOG_DEBUG, "removed line" . $rows[$i]);
                        unset($rows[$i]);
                    }

                }
            }
            else {
                //syslog(LOG_DEBUG, "needle not FOUND:" . $url_data);
            }

            // remove footer
            if (isset($remove_last_line)) {
                //syslog(LOG_DEBUG, "removed footer line" . end($rows));
                array_pop($rows);
                //syslog(LOG_DEBUG, "removed footer line2" . end($rows));
                array_pop($rows);
            }

            //add id to beginning
            foreach ($rows as $key => $line) {
                switch ($extraction['report_type']) {
                    case "STANDARD":
                    case "FLOODLIGHT":
                        $rows[$key] = $extraction['current_profileId'] . "," . $line;
                        break;

                    case "CROSS_DIMENSION_REACH":
                        $rows[$key] = $extraction['current_advertiserId'] . "," . $line;
                        break;
                }
            }

            $filter_data = implode("\n", $rows);
            return $filter_data . "\n";
            fwrite($fp, 'xxx');
            $pointer++;
        }

        fclose($fp);


    }

}
