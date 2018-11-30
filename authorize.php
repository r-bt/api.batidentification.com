<?php

  session_set_cookie_params(0, '/', '.batidentification.com');
  session_start();

  $client_name = "";

  if(!isset($_SESSION['id'])){
    $_SESSION['ref'] = 'https://api.batidentification.com/authorize.php?' . $_SERVER['QUERY_STRING'];
    header("Location: https://batidentification.com/login.php");
    exit('');
  }

  require("server.php");

  $request = OAuth2\Request::createFromGlobals();
  $response = new OAuth2\Response();

  if(!$server->validateAuthorizeRequest($request, $response)){
    $response->send();
    die;
  }

  if(!empty($_POST)){
    $is_authorized = ($_POST['authorized'] === 'yes');
    $server->handleAuthorizeRequest($request, $response, $is_authorized, $_SESSION['id']);
    $response->send();
  }else{

    $stmt = $connection->prepare("SELECT name FROM oauth_clients_information WHERE client_id = ?");
    $stmt->bind_param("s", $_GET['client_id']);
    $stmt->execute();
    $stmt->bind_result($client_name);
    $stmt->fetch();
    $stmt->close();

  }
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <base href="https://batidentification.com">
    <title>Authorize 3rd Party</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css?random=223.6">
    <script type="text/javascript" src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/batidentification.js"></script>
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
            <a>BatIdentification Project</a>
          </div>
        </div>
        <div class="collapse navbar-collapse" id="collapseable">
          <ul class="nav navbar-nav">
            <li><a href="https://batidentification.com">Home</a></li>
            <li><a>About us</a></li>
            <li><a id="brand-title">BatIdentification</a>
            <li><a href="https://batidentification.com/identify.php">Identify</a></li>
            <?php if(isset($_SESSION['id'])) : ?>
              <li><a href="https://batidentification.com/profile.php"><?php echo($_SESSION['username']); ?></a></li>
            <?php else : ?>
              <li><a href="https://batidentification.com/login.php">Login / Signup</a></li>
            <?php endif ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="content container-fluid">
      <div class="row box-container">
        <h5 id="warning-label"></h3>
        <div class="form-box col-md-4 col-md-offset-4">
          <div class="authorization-box">
            <h2><?php echo($client_name) ?></h2>
            <p>This app would like to:</p>
            <hr>
            <a>Upload bat calls</a>
            <hr>
            <a>Access previosuly uploaded bat calls</a>
            <hr>
            <a>Analyze calls on your behalf</a>
            <hr>
            <p>Would you like to give it access</p>
            <form method="post">
                <button type="submit" class="btn btn-primary" name="authorized" value="yes">Accept</button>
                <button type="submit" class="btn btn-danger" name="authorized" value="no">Reject</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </body>
</html>
