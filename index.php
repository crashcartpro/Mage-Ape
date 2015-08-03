<?php
#########################
#  Mage-Ape
#    v0.6 
# by CrashCart
#
# Mage-Ape is an atempt at a tool for testing and diagnosing errors with Magento API calls
#

ob_implicit_flush(1);

if (!empty($_POST)) {
  # Fetch POST variables
  $inputurl = $_POST['website'];
  $user = $_POST['user'];
  $pass = $_POST['pass'];
  #$apimehod = $_POST['apimethod'];
} else {  
  #defaults for testing
  $inputurl = "www.theath.simple-helix.net";
  $user = "theath";
  $pass = "donttell";
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Mage Ape</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<div class="container">
  <div class="pull-right">
    <img src="Mage_ape1.png">
  </div>
  <div class="col-sm-11 col-md-6 col-md-offset-2">
    <h1><a href="http://taoexmachina.com/mage-ape">Mage Ape</a><small>  Magento API test</small></h1>
    <div class="row">
      Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl
    </div>
    <div class-"row">
      <form action="" method="POST">
        <div class="form-group">
          <div class="input-group">
            <div class="input-group-addon">Site:</div>
            <input type="text" class="form-control" name="website" value="<?php echo $inputurl; ?>">
            <span class="input-group-btn">
              <button type="submit" class="btn btn-primary">Get Info</button>
            </span>
          </div>
        </div>
        <div class="input-group">
          <div class="input-group-addon">Username:</div>
          <input type="text" class="form-control" name="user" value="<?php echo $user;?>">
          <div class="input-group-addon">Password:</div>
          <input type="password" class="form-control" name="pass" value="<?php echo $pass; ?>">
          <!--<span class="input-group-btn">
            <button type="submit" class="btn btn-primary">Run Tests</button>
          </span>
        </div>
        <div class="input-group">
            <div class="input-group-addon">command:</div>
            <input type="text" class="form-control" name="apimethod" value="storeList">-->
        </div>
<?php
if (!empty($_POST)) {
  #loading gif here and visable
  ob_flush();

  # Filter URL. 
  # If http is missing, add http, 
  # If wsdl isn't declared, add wsdl and path
  $url = filter_var($inputurl, FILTER_SANITIZE_URL);
  if (strpos($url, "http://") !== 0) {$url = "http://" . $url;}
  $urlparts = parse_url($url);
  if (strpos($url, "wsdl") !== (strlen($url)-4)) {
    $url = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
  }
#  $headers = @get_headers($url);
#  echo "<div class='alert alert-warning' role='alert'>". var_dump($headers). "</div>";
  echo '<div class="alert alert-info" role="alert">Using: ' . $url . "</div>";
  ob_flush();
   

  # catch errors
  try {
    # setup SOAP client connection and login
    $client = new SoapClient($url);
    $session = $client->login($user, $pass);
    echo '<div class="alert alert-success" role="alert">Login successful. [' . $session . ']</div>';
    ob_flush();

    #try a few useful commands to gather data and show connection is working.
    $result = $client->storeList($session);
    echo '<div class="alert alert-info" role="alert">';
    #print_r($result);
    foreach ($result as $a) {
      echo "Store ID: " . $a->store_id . " Name: " . $a->code . "<br>";
    } 
    echo '</div>';
  }

#Error handling
catch(Exception $e) {
 echo '<div class="alert alert-danger" role="alert">' . $e->getMessage() . '</div>';
}
}
#I was going to ob_flush() here one last time but this is so close to the end of the file anyway.
?>
  </div>
</div>
</body></html>

