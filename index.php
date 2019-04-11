<!DOCTYPE html>
<html lang="en">
  <head>
    <title>BatIdentification</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/js/bootstrap-select.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
  </head>
  <body>
    <div class="navbar navbar-default">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#collapseable">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <div class="navbar-brand">
            <a>BatIdentification</a>
          </div>
        </div>
        <div class="collapse navbar-collapse" id="collapseable">
          <ul class="nav navbar-nav">
            <li><a id="index" href="/">Portal</a></li>
            <li><a id="about" href="data">Help</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="content container-fluid md-box">

        <div class="row">

          <form id="api-sandbox">
            <div class="col-md-6 input-group">

                <span class="input-group-addon">https://api.batidentification.com/api/</span>

                <select class="selectpicker from-control" data-width="100%" name="endpoint">
                  <option>calls</option>
                  <option>analyze</option>
                </select>

            </div>

            <div class="col-md-6 input-group">

              <span class="input-group-addon addon-no-left-border">?</span>

              <input class="form-control" name="params">

            </div>

            <input type="submit" class="btn btn-primary pull-right">

          </form>

        </div>

        <div class="row api-data-display">

          <pre id="api-data"></pre>

        </div>
      </div>

    </div>

    <script>

      $(document).ready(function(){

          $("#api-sandbox").submit(function(event){

            event.preventDefault();

            var params = {};

            var input = $(this).serializeArray()

            $.get("https://" + window.location.hostname + "/api/" + input[0].value + "?" + input[1].value, function(data){

                displayData(JSON.stringify(data, null, 1));

            });

          })

      });

      function displayData(data){

        $(".api-data-display").addClass("display-visible");

        $("#api-data").text(data);

      }

    </script>

  </body>
</html>
