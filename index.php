<?php
/*//error handler function
function customError($errno, $errstr) {
  echo "<p class='bg-danger'><b>Error:</b> [$errno] $errstr </p>";
  return;
}

//set error handler
set_error_handler("customError");
*/
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mage Ape</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<div class="container">
  <div class="col-sm-9 col-md-6 col-md-offset-1">
    <a href="http://taoexmachina.com/mage-ape"><h1>Mage Ape</h1></a>
    <div class="row">
      Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl
    </div>
    <div class-"row">
      <form action="" method="POST">
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">Site:</div>
            <input type="text" class="form-control" name="website">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary">Get Info</button>
            </span>
          </div>
        </div>
        <!--<div class="input-group">
            <div class="input-group-addon">Username:</div>
            <input type="text" class="form-control" name="user">
            <div class="input-group-addon">Password:</div>
            <input type="text" class="form-control" name="pass">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary">Run Tests</button>
            </span>
          </div>
      </form>-->
    </div>
<?php
if (!empty($_POST)) {
  $inputurl = $_POST['website'];
#  $inputurl = "banana.com";
  $url = filter_var($inputurl, FILTER_SANITIZE_URL);
  if (strpos($url, "http://") !== 0) {$url = "http://" . $url;}
  if (strpos($url, "wsdl") !== (strlen($url)-4)) {
    $urlparts = parse_url($url);
    $url = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
  }
  echo '<div class="alert alert-info" role="alert">Using: ' . $url . "</div>";
  try {
    $client = new SoapClient($url);
  echo '<div class="alert alert-success" role="alert">Started SOAP client.</div>';
  }

catch(Exception $e) {
 echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
}
}
?>
  </div>
  <div class="col-md-3">
    <img src="Mage_ape1.png">
  </div>
</div>
</body></html>

