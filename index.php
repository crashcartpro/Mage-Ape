<?php
#########################
#  Mage-Ape
#    v0.9.5 
# by CrashCart
#
# Mage-Ape is an atempt at a tool for testing and diagnosing errors with Magento API calls
#

#set environment variables
ini_set("default_socket_timeout", 6000);
ob_implicit_flush(1);
$userpath = true;
$redirected = false;

function correctURL($inputurl) {
	# If http is missing, add http, 
	# If wsdl isn't declared, add wsdl and path
	global $apimethod;
	global $userpath;
	
	$workingurl = filter_var($inputurl, FILTER_SANITIZE_URL);
	if (strpos($workingurl, "http") !== 0) {$workingurl = "http://" . $workingurl;}
	$urlparts = parse_url($workingurl);
	if (!isset($urlparts["path"])) {
		$userpath = false;
		if ($apimethod == "m1_soap1"){
			$workingurl = "http://" . $urlparts["host"] . "/index.php/api/soap/?wsdl";    
		} elseif ($apimethod == "m1_soap2"){
			$workingurl = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";    
		}
	}
	return $workingurl;
}

function getFromHttp($target_url) {
	$ch = curl_init($target_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, true);
	$r = curl_exec($ch);
	$rinfo = curl_getinfo($ch);
	$hcode = $rinfo['http_code'];
	$new_target = $rinfo['redirect_url'];
	$htext = strtok($r, "\n");
	$body = substr($r, $rinfo['header_size']);
	curl_close($ch);
	return array("code" => $hcode, "head" => $htext, "body" => $body, "redirect" => $new_target);
}


function postMessage($type, $title, $message = "none") {
	global $starttime;
	$timestamp = round(microtime(true)-$starttime,2);
	echo '<div class="alert '.$type.'" role="alert">';
	echo '<div style="float:right">'.$timestamp.'s</div>';
	echo '<h4>'.$title.'</h4>';
	if ($message != "none") {echo $message;}
	echo "</div>\r\n";
}

if (!empty($_POST)) {
	## Fetch POST variables
	$inputurl = $_POST['website'];
	$user = $_POST['user'];
	$pass = $_POST['pass'];
	$apimethod = $_POST['apimethod'];
} else {  
	## Default veribles for testing
	$inputurl = "www.theath.simple-helix.net";
	$user = "theath";
	$pass = "donttell";
	$apimethod = "m1_soap2";
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mage Ape</title>
	<!--<link rel="stylesheet" href="includes/bootstrap_3.3.5_min.css">-->
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="includes/bootstrap_4.0.0_alpha2.css">
	<script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
	<script src="includes/bootstrap_4.0.0_alpha2.js"></script>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-xs-12 col-md-4 text-xs-center text-md-left pull-md-right">
      <img src="Mage_ape1.png" style="width:100%;max-width:220px;"><br>
    </div>
    <div class="col-sx-12 col-md-7 offset-lg-1">
      <h1><a href="http://taoexmachina.com/mage-ape">Mage Ape</a><small> Magento&nbspAPI&nbsptest</small></h1>
      <p>Magento is a highly extensable e-ecomerce framework with many moving parts. Just one such part is the SOAP or XML-RPC based API interface. Which allows 3rd party programs to access store content.</p>
      <p>However, sometimes things fail. Mage Ape wants to help you troubleshoot.</p>
      <p>Start by entering your domain. <code>example.com</code> Mage Ape will assume defaults and test unauthenticated requests. You can also specify the full path to the WSDL. <code>www.example.com/index.php/api/v2_soap/?wsdl</code></p>
      <p>If you specify the user and password, Mage Ape will start a session and try to pull some data from the store. If any step fails, Mage Ape dutifully prints the error message for you you see.</p>
    </div>
  </div>
  <div class="row">
    <div class="col-xs-12 col-lg-8 offset-lg-2">
      <p>Try: http://www.theath.simple-helix.net/index.php/api/v2_soap/?wsdl</p>
      <form action="" method="POST">
        <div class="form-group">
          Method
          <div class="btn-group" data-toggle="buttons">
            <label class="btn btn-outline-primary <?php if($apimethod=="m1_soap1"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m1_soap1" id="optionA1" <?php if($apimethod=="m1_soap1"){echo"checked";}?>>Magento 1.x SOAP V1</label>
            <label class="btn btn-outline-primary <?php if($apimethod=="m1_soap2"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m1_soap2" id="optionA2" <?php if($apimethod=="m1_soap2"){echo"checked";}?>>Magento 1.x SOAP V2</label>
            <label class="btn btn-outline-danger <?php if($apimethod=="m2_soap"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m2_soap" id="optionA3" <?php if($apimethod=="m2_soap"){echo"checked";}?>>Magento 2.x SOAP</label>
          </div>
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
      <p>
<?php


if (!empty($_POST)) {
	## loading gif 
	echo '<img id="loadingGif" style="position:absolute;bottom:-20px;left:46%;" src="ape-loader2.gif">';
	ob_flush();
	$starttime = microtime(true);
## Filter URL. 
	$url = correctURL($inputurl);
	postMessage("alert-info", "Started test using ".($userpath?"path you specified":"default path"), $url);
	ob_flush();

## check link response
	$url_response = getFromHttp($url);
	$msgtype = "alert-danger"; #if it ain't good, it's bad.
	$msg = $url_response['head'];
	if ($url_response['code'] < 200) { # 0 - 199 Progress messages. would be wierd to get, but ok.
		$msgtype = "alert-warning";
	} elseif ($url_response['code'] >=200 && $url_response['code'] < 300) { # 200 - 299
		$msgtype = "alert-success";
	} elseif ($url_response['code'] >=300 && $url_response['code'] < 400) { # 300 - 399 redirect
		$msgtype = "alert-warning";
		$followup = getFromHttp($url_response['redirect']);
		$msg .= "<br>&#8627;&nbsp;".$url_response['redirect'];
		$msg .= "<br>&nbsp;&#8627;&nbsp;Returns:&nbsp;".$followup['head'];
	} # 400 and up basically everything else. either not found, forbiden, or server error.
	postMessage($msgtype, "Request returned:", $msg);
	ob_flush(); 

## Validate WSLD

	

## Create soap conneciton and run tests
	try {
	$options = array('exceptions'=>true, 'trace'=>1, 'cache_wsdl' => WSDL_CACHE_NONE);
	$client = new SoapClient($url, $options);
	
	
	if (empty($user) || empty($pass)) {
		$session = $client->startSession();
		postMessage("alert-info", "Started session without auth", "Session ID: ".$session);
	} else {
		$session = $client->login($user, $pass);
		postMessage("alert-success", "Login successful", "Session ID: ".$session);
	}
	ob_flush();

		#try a few useful commands to gather data and show connection is working.
	if ($apimethod == "m1_soap1") {
		$result = $client->call($session, 'core_magento.info');
                $msg = $result['magento_edition'] . " edition " . $result['magento_version'];
                postMessage("alert-info", "Version:", $msg);
                ob_flush();

                $msg = "";
                $result = $client->resources($session);
                foreach ($result as $a) {
                        $msg = $msg . $a['title'] . "<br>";
                }
                postMessage("alert-info", "Available resources:", $msg);
		ob_flush();
	} elseif ($apimethod == "m1_soap2") {	
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
	}

	#Error handling
	catch(Exception $e) {
		$timestamp = round(microtime(true)-$starttime,2);
		echo '<div class="alert alert-danger" role="alert">';
		echo '<div style="float:right">'.$timestamp.'s</div>';
		echo '<h4>Catch Error</h4>';
		## Profile error
		if (is_a($e, 'SoapFault')) {
			## object type denotes SOAP error returned
			echo $e->faultstring . '<br>';
			if ($e->faultstring == 'Access denied.') {
				echo 'The user <strong>' . $user . '</strong> cannot ';
				foreach ($e->getTrace() as $traced) { 
					echo '-&gt;<strong>' . $traced['function'] . '</strong>';
					
				}
				if ($traced['funciton'] == "login") {
					echo '<br>check username and password.';
				} else {
					echo "<br>Have you checked the user's roll?";
				}
			}
		} else {
			echo $e->getMessage();
		}
		echo '<hr><pre>';
		var_dump($e);
		echo "</pre></div>\r\n";
	}

}
?>
      </p>
    </div>
  </div>
</div>

<script>document.getElementById('loadingGif').style.display = 'none';</script>
</body></html>

