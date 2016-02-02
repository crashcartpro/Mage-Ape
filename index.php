<?php
#########################
#  Mage-Ape
#    v0.8 
# by CrashCart
#
# Mage-Ape is an atempt at a tool for testing and diagnosing errors with Magento API calls
#

#set environment variables
ini_set("default_socket_timeout", 6000);
ob_implicit_flush(1);

function correctURL($inputurl) {
	# If http is missing, add http, 
	# If wsdl isn't declared, add wsdl and path
	$workingurl = filter_var($inputurl, FILTER_SANITIZE_URL);
	if (strpos($workingurl, "http") !== 0) {$workingurl = "http://" . $workingurl;}
	$urlparts = parse_url($workingurl);
	if (strpos($workingurl, "wsdl") !== (strlen($workingurl)-4)) {
		$workingurl = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
	}
	return $workingurl;
}

function postMessage($type, $title, $message) {
	global $starttime;
	$timestamp = round(microtime(true)-$starttime,2);
	echo '<div class="alert '.$type.'" role="alert">';
	echo '<div style="float:right">'.$timestamp.'</div>';
	echo '<h4>'.$title.'</h4>';
	if (!empty($message)) {echo $message;}
	echo "</div>\r\n";
}

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
	<div class="col-sx-12 col-sm-6 col-md-6 col-md-offset-2">
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
	$starttime = microtime(true);
## Filter URL. 
	$url = correctURL($inputurl);
	postMessage("alert-info", "Started test using:", $url);
	ob_flush();

## check link response
	$headers = get_headers($url,1);
	if (strpos($headers[0], "200")){
		postMessage("alert-success", "URL is ok");
	} elseif (strpos($headers[0], "301") or strpos($headers[0], "302")){
		postMessage("alert-danger", "URL redirects to:", $headers["Location"]);
	} elseif (strpos($headers[0], "404")){
		postMessage("alert-danger", "URL path does not exist");
	} else {
		postMessage("alert-danger", "URL returns a bad request");
	}
	ob_flush(); 

	# catch errors
	try {
		# setup SOAP client connection and login
		$options = array('exceptions'=>true, 'trace'=>1);
		$client = new SoapClient($url, $options);
		if (empty($user) || empty($pass)) {
			$session = $client->startSession();
			postMessage("alert-info", "Started session without auth", $session);
		} else {
			$session = $client->login($user, $pass);
			postMessage("alert-success", "Login successful", $session);
		}
		ob_flush();

		#try a few useful commands to gather data and show connection is working.
		$result = $client->magentoInfo($session);
		$msg = $result->magento_edition . " edition " . $result->magento_version;
		postMessage("alert-info", "Version:", $msg);
		ob_flush();

		$msg = "";
		$result = $client->resources($session);
		foreach ($result as $a) {
			$msg = $msg . $a->title . "<br>";
		}
		postMessage("alert-info", "Available resources:", $msg);
		ob_flush();

	}

	#Error handling
	catch(Exception $e) {
		$timestamp = round(microtime(true)-$starttime,2);
		echo '<div class="alert alert-danger" role="alert">';
		echo '<div style="float:right">'.$timestamp.'</div>';
		echo '<h4>Catch Error</h4>';
		echo $e->getMessage() . '<hr><pre>';
		var_dump($e);
		echo "</pre></div>\r\n";
	}

}
?>
		</div>
	</div>
</div>
</body></html>

