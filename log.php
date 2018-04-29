<?php



if ($_SERVER['HTTP_HOST'] === 'localhost:8080' )  {
    $bucket = "api-extractor-staging";
    $filename = "config-global.php";
}

else if (strpos($_SERVER['HTTP_HOST'], 'staging') !== false )  {
    $bucket = "api-extractor-staging";
    $filename = "gs://$bucket/config/config-global.php";
}

else {
    $bucket = "annalect-dashboarding";
    $filename = "gs://$bucket/config/config-global.php";
}


require_once __DIR__ . '/api/helpers.php';
$helpers = new helpers();
$config_global = file_get_contents($filename);
$config_global = str_replace("<?php", '', $config_global);
eval($config_global);
$extraction['global'] = $extractions['global'] ;


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0"/>
    <title>Annalect API data extractor</title>

    <!-- materialize  -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/css/materialize.css" type="text/css" rel="stylesheet" media="screen,projection"/>
    <link href="assets/css/style.css" type="text/css" rel="stylesheet" media="screen,projection"/>
    <!-- codemirror  -->
    <link rel="stylesheet" href="assets/codemirror/lib/codemirror.css">
    <link rel="stylesheet" href="assets/codemirror/addon/display/fullscreen.css">
    <link rel="stylesheet" href="assets/codemirror/theme/night.css">

    <style>

        table {
            font-size: 11px;
        }
        td, th {
            padding: 3px;
        }
    </style>


</head>
<body>

<?php

    if (isset($_GET['d'])) :

    $file = urldecode($_GET['d']);
    $title = "log setup: $file";

    if ($_SERVER['HTTP_HOST'] === 'localhost:8080' )  {
        $log_file = file_get_contents($_GET['d']);
    } else {
        $log_file = file_get_contents("gs://$bucket/" . $_GET['d']);
    }

    $rows= explode("\n", $log_file );
    $logs = [];
    foreach ($rows as $row) {
        $logs[] = explode(",", $row );
    }
    $logs = $helpers->merge_array($logs, 4);

?>

<nav class="teal lighten-1" role="navigation">
    <div class="nav-wrapper container"><a id="logo-container" href="#" class="brand-logo">aio_phd - <?php echo $_GET['d']; ?></a>
    </div>
</nav>
<div class="section no-pad-bot" id="index-banner">
    <div class="container">

        <br><br>

        <div class="row">
            <ul class="collapsible">
                <?php  foreach ($logs as $source_id => $source_log )  :  ?>

                <li>
                    <div class="collapsible-header"><i class="material-icons">view_headline</i><?php echo $source_id; ?></div>
                    <div class="collapsible-body">
                        <p>

                        <?php
                            $final_files = 0;
                            $extraction_logs = $helpers->merge_array($source_log, 5);
                            foreach ($extraction_logs as $extraction_id => $extraction_log )  :
                                foreach ($extraction_log as $row )  :
                                    if (in_array("FINAL FILE", $row)) {
                                        $current_size = round($row[10] / 1024 / 1024, 2);
                                        $prev_size = round($row[11] / 1024 / 1024, 2);
                                        $file_name = $row[9].".csv";
                                        $action = $row[8];
                                        $diff_per = round((($current_size - $prev_size) / $current_size )* 100, 2);
                                        echo "$file_name : $current_size Mb($diff_per%) - $action <br>";
                                        $final_files++;
                                    }
                                endforeach;
                            endforeach;

                            if (!$final_files) echo "File/s not generated";
                        ?>

                        </p>

                        <ul class="collapsible">
                            <?php
                                    foreach ($extraction_logs as $extraction_id => $extraction_log )  :
                             ?>

                            <li>
                                <div class="collapsible-header"><i class="material-icons">view_headline</i><?php echo $extraction_id; ?></div>
                                <div class="collapsible-body">

                                    <table class="highlight">
                                        <tbody>
                                        <?php foreach ($extraction_log as $col )  :   ?>
                                        <tr>
                                            <?php foreach ($col as $val )  :   ?>
                                                <td><?php echo $val; ?></td>
                                            <?php  endforeach;  ?>
                                        </tr>
                                        <?php  endforeach;  ?>
                                        </tbody>
                                    </table>
                                </div>
                            </li>

                            <?php  endforeach;  ?>


                        </ul>
                    </div>
                </li>

                <?php  endforeach;  ?>

            </ul>


        </div>

        <div class="row">
            <h5 class="header">Info:</h5>

            <p><a href="https://docs.google.com/spreadsheets/d/<?php echo $extractions['global']['google_sheet']['sheet_id'];?>/edit#gid=0"
                  target="_blank">Google Spreadsheets : Real Time log</a></p>
            <p> Check versions : <br>
            gsutil ls -la gs://annalect-dashboarding/config/phd-aio.php<br>
            https://cloud.google.com/storage/docs/gsutil/commands/ls
            </p>

        </div>

        <div class="row ">
            <h5 class="header">Other log setup:</h5>

            <?php

                $log_files = $helpers->bucket_listing ($extraction, 'log');
                foreach (array_reverse($log_files) as $file){
                    echo "<p> <a href='?d=$file->name'>".urldecode($file->name)."</a> </p>";
                }

            ?>

        </div>


    </div>
</div>














<?php else: ?>



    <nav class="teal lighten-1" role="navigation">
        <div class="nav-wrapper container"><a id="logo-container" href="#" class="brand-logo">select one file </a>
        </div>
    </nav>
    <div class="section no-pad-bot" id="index-banner">
        <div class="container">

            <br><br>

            <div class="row ">
                <h5 class="header">Select file:</h5>
                <?php

                    $log_files = $helpers->bucket_listing ($extraction, 'log');
                    foreach (array_reverse($log_files) as $file){
                        echo "<p> <a href='?d=$file->name'>".urldecode($file->name)."</a> </p>";
                    }

                ?>
            </div>


        </div>
    </div>



<?php endif; ?>



<script
        src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
        crossorigin="anonymous"></script>
<!--  Scripts-->
<script src="assets/js/materialize.js"></script>
<script src="assets/js/init.js"></script>
<script>

    $('.modal').modal({
            dismissible: true, // Modal can be dismissed by clicking outside of the modal
            opacity: .5, // Opacity of modal background
            inDuration: 300, // Transition in duration
            outDuration: 200, // Transition out duration
            startingTop: '4%', // Starting top style attribute
            endingTop: '10%'
        }
    );

</script>
</body>
</html>
