<!DOCTYPE html>
<html>
<head>
  <title>Mage Ape</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head>
<body>
<div class="container">
  <div class="col-md-6 col-md-offset-1">
    <a href="http://taoexmachina.com/mage-ape"><h1>Mage Ape</h1></a>
    <div class="row">
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
      </form>
    </div>
    <div class="row bg-info">
      Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl
    </div>
    <div class="row">
<?php
if (!empty($_POST)) {
  $inputurl = $_POST['website'];
  $url = filter_var($inputurl, FILTER_SANITIZE_URL);
  if (strpos($url, "http://") !== 0) {$url = "http://" . $url;}
  $urlparts = parse_url($url);
  print_r($urlparts);
  if (!isset($urlparts["path"])) {$urlparts["path"] = "/index.php/api/v2_soap/";}
  if (!isset($urlparts["query"])) {$urlparts["query"] = "wsdl";}
  echo "<br>";
  print_r($urlparts);
  var_dump($urlparts);
  #$targeturl = http_build_url($$urlparts);
  #print_r($targeturl); 
  /*if (($response_xml_data = file_get_contents($inputurl))===false){
    echo "Error fetching XML\n";
  } else {
    print_r($response_xml_data);
  }*/
  /*$client = new SoapClient($inputurl);
  $session = $client->login('theath', 'donttell');
  print_r($session);
  $result = $client->magentoInfoEntity($session);
  print_r($result);*/
}
?>
    </div>
  </div>
  <div class="col-md-3">
    <img src="Mage_ape1.png">
  </div>
</div>
</body></html>
