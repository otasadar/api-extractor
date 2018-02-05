<?php
/*

$newFileContent = 'hola don pepito';

$default_bucket = CloudStorageTools::getDefaultGoogleStorageBucketName();
$fp = fopen("gs://${default_bucket}/hello_default_stream.txt", 'w');
fwrite($fp, $newFileContent);
fclose($fp);

$fp2 = fopen("gs://${default_bucket}/hello_default_stream.txt", 'r');


echo $fp2;
*/

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <!-- Title -->
    <title>Annalect Data Platform</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <meta name="description" content="Annalect Data Platform"/>
    <meta name="robots" content="noindex,nofollow">
    <meta name="author" content="Annalect Dubai Team"/>

    <!-- Styles -->
    <link type="text/css" rel="stylesheet"
          href="http://localhost:8080/assets/plugins/materialize/css/materialize.min.css"/>
    <link href="https://fonts.googleapis.com/css?family=ABeeZee|Cantarell|Comfortaa|Quattrocento+Sans|Questrial" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="http://localhost:8080/assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="http://localhost:8080/assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" href="http://localhost:8080/assets/plugins/materialize-stepper/materialize-stepper.min.css">

    <link href="http://localhost:8080/assets/plugins/scroll/css/jquery.mCustomScrollbar.min.css" rel="stylesheet" type="text/css"/>
    <link href="http://localhost:8080/assets/plugins/select2/css/select2.min.css" rel="stylesheet" type="text/css"/>
    <link href="http://localhost:8080/assets/plugins/select2/select2-materialize.css" rel="stylesheet" type="text/css"/>

    <!-- Theme Styles -->
    <link href="http://localhost:8080/assets/css/alpha.css" rel="stylesheet" type="text/css"/>
    <link href="http://localhost:8080/assets/css/annalect.css" rel="stylesheet" type="text/css"/>
    <link rel='Shortcut Icon' href='http://localhost:8080/assets/img/favicon.ico' type='image/x-icon'/>


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="http://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="http://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link href="http://localhost:8080/assets/css/font-awesome.min.css" rel="stylesheet" type="text/css"/>

</head>
<body class="signin-page  grey lighten-4 loaded">



<div class="row">
    <form class="col s12" action="save" method="post">
        <div class="row">
            <a href="save?restore=true" class="waves-effect waves-light btn">Restore</a>
        </div>
        <div class="row">
            <div class="input-field col s12">

                <input type="hidden" id="hola" name="hola" value="123">
                <textarea id="html_content" name="html_content" class="materialize-textarea" data-length="120">

                    <?php echo file_get_contents("gs://staging.annalect-platform.appspot.com/config.txt"); ?>



                </textarea>

                <input type="submit" class="waves-effect waves-light btn" value="Save">


            </div>
        </div>
    </form>
</div>


<!-- Javascripts -->
<script src="http://localhost:8080/assets/plugins/jquery/jquery-2.2.0.min.js"></script>

<link href="http://localhost:8080/assets/plugins/animate/animate.css" rel="stylesheet">

<script src="http://localhost:8080/assets/plugins/jquery-ui/jquery-ui.min.js"></script>
<script src="http://localhost:8080/assets/plugins/materialize/js/materialize.min.js"></script>

<script src="http://localhost:8080/assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
<script src="http://localhost:8080/assets/plugins/jquery-blockui/jquery.blockui.js"></script>

<script src="http://localhost:8080/assets/plugins/select2/js/select2.min.js"></script>
<script src="http://localhost:8080/assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="http://localhost:8080/assets/plugins/date/date.js"></script>
<script src="http://localhost:8080/assets/plugins/clipboard/clipboard.min.js"></script>
<script src="http://localhost:8080/assets/plugins/jquery-validator/jquery-min.js"></script>
<script src="http://localhost:8080/assets/plugins/materialize-stepper/materialize-stepper.min.js"></script>
<script src="https://apis.google.com/js/api.js"></script>
<script src="http://localhost:8080/assets/js/alpha.js"></script>
<script src="http://localhost:8080/assets/plugins/scroll/js/jquery.mCustomScrollbar.concat.min.js"></script>


</body>
</html>








