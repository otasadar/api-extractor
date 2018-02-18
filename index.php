<?php

    require_once __DIR__ . '/api/helpers.php';
    $helpers = new helpers();
    // todo add config from parameter

    if (isset($_GET['config'])){
        $title = "Config setup: {$_GET['config']}";
        $config_file = file_get_contents("gs://annalect-dashboarding/config/".$_GET['config']);
    } else {
        $title = "Config setup";
        $config_file = file_get_contents("gs://annalect-dashboarding/config/latest.php");
    }

    eval($config_file);

    $bucket= 'annalect-dashboarding';
    $access_token = $helpers->get_storage_access_token($extractions);
    $headers = array( 'Authorization : Bearer ' . $access_token, 'Accept: application/json');
    $endpoint = "https://www.googleapis.com/storage/v1/b/$bucket/o?prefix=config";
    $response= $helpers->set_curl($headers, $endpoint, null, 'GET', null);
    $response = json_decode($response);

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

</head>
<body>

  <nav class="teal lighten-1" role="navigation">
    <div class="nav-wrapper container"><a id="logo-container" href="#" class="brand-logo">API extractor</a>
    </div>
  </nav>
  <div class="section no-pad-bot" id="index-banner">
    <div class="container">

      <br><br>

      <div class="row center">
        <h5 class="header center">Config setup</h5>
      </div>

      <div class="row">
        <textarea id="code" name="code" rows="5">
<?php echo '<?php'."\n\n";
echo $config_file;
?>
        </textarea>
      </div>

      <div class="row center">
        <span id="run" class="btn-large waves-effect waves-light modal-trigger" href="#modal1">Save & Run</span>

        <!-- Modal Structure -->
        <div id="modal1" class="modal">
          <div class="modal-content">
              <h4>Task process.</h4>
              <p id="loading">Loading....</p>
              <div id="showresults"></div>

          </div>
          <div class="modal-footer">
              <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat">Close</a>
          </div>
        </div>

      </div>

      <div class="row center">
        <p> <a href="https://docs.google.com/spreadsheets/d/1oUslYYAHVtqTwqUSHsPXkXH4EDNC-JGiXPQQAiyrQc0/edit#gid=0" target="_blank">Real Time log</a> </p>
      </div>

        <div class="row ">
            <h5 class="header">Other config setup:</h5>
            <?php
            foreach ($response->items as $key=> $row) {
                $file_name =str_replace("https://www.googleapis.com/storage/v1/b/annalect-dashboarding/o/config", "", $row->selfLink);
                echo "<p> <a href='?config=$file_name'>$file_name</a> - <a href='https://storage.googleapis.com/annalect-dashboarding/config$file_name' target='_blank'>View</a></p>";
            }
            ?>
        </div>

    </div>
  </div>



  <!--  codemirror -->
  <script src="assets/codemirror/lib/codemirror.js"></script>
  <script src="assets/codemirror/mode/php/php.js"></script>
  <script src="assets/codemirror/addon/display/fullscreen.js"></script>
  <script src="assets/codemirror/addon/edit/matchbrackets.js"></script>
  <script src="assets/codemirror/htmlmixed/htmlmixed.js"></script>
  <script src="assets/codemirror/xml/xml.js"></script>
  <script src="assets/codemirror/javascript/javascript.js"></script>
  <script src="assets/codemirror/css/css.js"></script>
  <script src="assets/codemirror/clike/clike.js"></script>
  <script
          src="https://code.jquery.com/jquery-2.2.4.min.js"
          integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="
          crossorigin="anonymous"></script>
  <!--  Scripts-->
  <script src="assets/js/materialize.js"></script>
  <script src="assets/js/init.js"></script>
  <script>
      var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
          lineNumbers: true,
          matchBrackets: true,
          mode: "application/x-httpd-php",
          indentUnit: 4,
          foldGutter: true,
          indentWithTabs: true,
          extraKeys: {
              "F11": function(cm) {
                  cm.setOption("fullScreen", !cm.getOption("fullScreen"));
              },
              "Esc": function(cm) {
                  if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false);
              }
          }
      });

      var currentURL = new URL(window.location.href);

      $('#run').click(function(event) {

          $.ajax({
              url: 'run',
              type: 'post',
              data: {
                  'code': editor.getValue(),
                  'config': currentURL.searchParams.get("config")
              },
              before:function (response) {
                  $('#modal1').modal('open');
              },
              success: function (response) {
                  $('#loading').remove();
                  //var result = $('<div />').append(response).find('#showresults').html();
                  $('#showresults').html(response);
              },
              error: function (xhr, status, error) {}
          });

      });

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
