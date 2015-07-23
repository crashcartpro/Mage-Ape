<!DOCTYPE html>
<html><head>
  <title>Mage Ape</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
</head><body>
<div class="container">
  <p><div class="bg-info">Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl</div></p>
  <form clase="form-inline" action="">
    <div class="form-group">
      <div class="input-group">
        <div class="input-group-addon">Site:</div>
        <input type="text" class="form-control" name="website">
      </div>
      <button type="submit" class="btn btn-primary">Submit"</button>
    </div>
  </form>
  <p>
<?php
if (!empty($_GET)) {
  $inputurl = $_GET['website'];
  /*if ($targeturl = parse_url($inputurl)) {
    print_r($targeturl);
  } else {
    echo "<div class='alert alert-danger'>Bad input</div>";
  }*/
  if (($response_xml_data = file_get_contents($inputurl))===false){
    echo "Error fetching XML\n";
  } else {
    print_r($response_xml_data);
  }
}
?>
  </p>
</div>
</body></html>
