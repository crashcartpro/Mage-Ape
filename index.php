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
  ## Fetch POST variables
  $inputurl = $_POST['website'];
  $user = $_POST['user'];
  $pass = $_POST['pass'];
#  $apimehod = $_POST['apimethod'];	#for future use with API method switching
} else {  
  ## Default veribles for testing
  $inputurl = "www.theath.simple-helix.net";
  $user = "theath";
  $pass = "donttell";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mage Ape</title>
	<link rel="stylesheet" href="includes/bootstrap.min.css">
	<script src"includes/bootstrap.min.js"></script>
</head>
<body>
<div class="container">
	<div class="pull-right">
		<img src="Mage_ape1.png">
	</div>
	<div class="col-sm-11 col-md-6 col-md-offset-2">
		<h1><a href="http://taoexmachina.com/mage-ape">Mage Ape</a><small>  Magento API test</small></h1>
		<p>Magento is a highly extensable e-ecomerce framework with many moving parts. Just one such part is the SOAP or XML-RPC based API interface. Which allows 3rd party programs to access store content.</p>
		<p>However, sometimes things fail. Mage Ape wants to help you troubleshoot.</p>
		<p>Start by entering your domain. <code>example.com</code> Mage Ape will assume defaults and test unauthenticated requests. You can also specify the full path to the WSDL. <code>www.example.com/index.php/api/v2_soap/?wsdl</code></p>
		<p>If you specify the user and password, Mage Ape will start a session and try to pull some data from the store. If any step fails, Mage Ape dutifully prints the error message for you you see.</p>
		<div class="row">Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl</div>
		<div class-"row">
			<form action="" method="POST">
				<div class="form-group">
					<div class="input-group">
						<div class="input-group-addon">URL:</div>
						<input type="text" class="form-control" name="website" value="<?php echo $inputurl; ?>">
						<span class="input-group-btn">
							<button type="submit" class="btn btn-primary">Get Info</button>
						</span>
					</div>
					<div class="input-group">
						<div class="input-group-addon">Username:</div>
						<input type="text" class="form-control" name="user" value="<?php echo $user;?>">
						<div class="input-group-addon">Password:</div>
						<input type="password" class="form-control" name="pass" value="<?php echo $pass; ?>">
					</div>
				</div>
			</form>
		</div>
		<div class-"row">

<?php
if (!empty($_POST)) {
	## loading gif here? and visable
	ob_flush();

## Filter URL. 
	# If http is missing, add http, 
	# If wsdl isn't declared, add wsdl and path
	$url = filter_var($inputurl, FILTER_SANITIZE_URL);
	if (strpos($url, "http://") !== 0) {$url = "http://" . $url;}
	$urlparts = parse_url($url);
	if (strpos($url, "wsdl") !== (strlen($url)-4)) {
		$url = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
	}
	echo '<div class="alert alert-info" role="alert">Started tests using:<br/>' . $url . "</div>";
	ob_flush();

## check link response
	$headers = get_headers($url,1);
	if (strpos($headers[0], "200")){
		echo '<div class="alert alert-success" role="alert">';
		echo "URL is ok.";
	} elseif (strpos($headers[0], "301") or strpos($headers[0], "302")){
		echo '<div class="alert alert-warning" role="alert">';
		echo "URL redirects to: " . $headers["Location"] . "<br>";
		$url = $headers["Location"] . "index.php/api/v2_soap/?wsdl";
		echo "Continuing with: " . $url;
	} elseif (strpos($headers[0], "404")){
		echo '<div class="alert alert-danger" role="alert">';
		echo "URL path does not exist.";
	} else {
		echo '<div class="alert alert-danger" role="alert">';
		echo "URL returns a bad request.<br>" . $headers[0];
	}
	echo "</div>";
	ob_flush(); 

## check wsdl data
	
	

	# catch errors
	try {
		# setup SOAP client connection and login
		$client = new SoapClient($url);
		if (empty($user) || empty($pass)) {
			$session = $client->startSession();
			echo '<div class="alert alert-info" role="alert">Started session without auth.<br>';
			echo 'Session ID: ' . $session . '</div>';
		} else {
			$session = $client->login($user, $pass);
			echo '<div class="alert alert-success" role="alert">Login successful.<br>';
			echo 'Session ID: ' . $session . '</div>';
		}
		ob_flush();

		#try a few useful commands to gather data and show connection is working.
		$result = $client->magentoInfo($session);
		echo '<div class="alert alert-info" role="alert">';
		echo $result->magento_edition . " Magento version " . $result->magento_version;
		echo '</div>';
		ob_flush();

		$result = $client->storeList($session);
		echo '<div class="alert alert-info" role="alert">';
		foreach ($result as $a) {
			echo "Store ID: " . $a->store_id . " Name: " . $a->code . "<br>";
		} 
		echo '</div>';
		ob_flush();

		$result = $client->resources($session);
		echo '<div class="alert alert-info" role="alert">';
		foreach ($result as $a) {
			echo $a->title . "<br>";
		}
		echo '</div>';
		ob_flush();

#
# Calls that only require session id
#
# resources
# globalFaults
# resourceName
# storeList
# magentoInfo
# directoryCountryList
# customerGroupList
# catalogProductAttributeSetList
# catalogProductAttributeTypes
# catalogProductTypeList
# catalogProductLinkTypes
# catalogProductCustomOptionTypes
# catalogCategoryAttributeList
#
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
</div>
</body></html>

