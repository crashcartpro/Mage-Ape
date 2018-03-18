<?php

require '/var/www/Mage-Ape/kint.php';
#########################
#  Mage-Ape
#    v1.0
# by CrashCart
#
# Mage-Ape is an atempt at a tool for testing and diagnosing errors with Magento API calls
#
# *** logic of the test ***
# 1) user provides: api endpoint and api authentication.
# 2) validate user input.
# 3) check response of endpoint.
# 4) check content of response. (wsdl, schema, html?)
# 5) instantiate client connection and login.
# 6) pull info from API call.
# 7) do more API calls to be sure.
#

// set environment variables
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set("default_socket_timeout", 6000);
ob_implicit_flush(1);

define("SUCCESS", "alert-success");
define("INFO", "alert-info");
define("WARNING", "alert-warning");
define("DANGER", "alert-danger");

$userpath   = true;
$redirected = false;

function correctURL($inputurl)
{
    // If http is missing, add http,
    // If wsdl isn't declared, add wsdl and path
    global $apimethod;
    global $userpath;

    $workingurl = filter_var($inputurl, FILTER_SANITIZE_URL);
    if (strpos($workingurl, "http") !== 0) {
        $workingurl = "http://" . $workingurl;
    }
    $urlparts   = parse_url($workingurl);
    if (!isset($urlparts["path"])) {
        $userpath = false;
        if ($apimethod == "m1_soap1") {
            $workingurl = "http://" . $urlparts["host"] . "/index.php/api/soap/?wsdl";
        } elseif ($apimethod == "m1_soap2") {
            $workingurl = "http://" . $urlparts["host"] . "/index.php/api/v2_soap/?wsdl";
        } elseif ($apimethod == "m2_soap") {
	    $workingurl = "http://" . $urlparts["host"];
        } elseif ($apimethod == "m2_rest") {
            #$workingurl = "http://" . $urlparts["host"] . "/index.php/rest/default/schema";
        }
    }
    return $workingurl;
}

function getFromHttp($target_url)
{
    $ch = curl_init($target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    $r          = curl_exec($ch);
    $rinfo      = curl_getinfo($ch);
    $hcode      = $rinfo['http_code'];
    $new_target = $rinfo['redirect_url'];
    $htext      = strtok($r, "\n");
    $body       = substr($r, $rinfo['header_size']);
    curl_close($ch);
    return array("code" => $hcode, "head" => $htext, "body" => $body, "redirect" => $new_target);
}


function postMessage($type, $title, $message = "none")
{
    global $starttime;
    $timestamp = round(microtime(true)-$starttime, 2);
    echo '<div class="alert '.$type.'" role="alert">';
    echo '<div style="float:right">'.$timestamp.'s</div>';
    echo '<h4>'.$title.'</h4>';
    if ($message != "none") {
        echo $message;
    }
    echo "</div>\r\n";
}

if (!empty($_POST)) {
    // Fetch POST variables
    $inputurl  = $_POST['website'];
    $user      = $_POST['user'];
    $pass      = $_POST['pass'];
    $apimethod = $_POST['apimethod'];
} else {
    // Default veribles for testing
#    $inputurl  = "magento2demo.firebearstudio.com";
#    $user      = "demo";
#    $pass      = "1q2w3e4r";
#    $apimethod = "m1_soap2";

    $inputurl  = "mage2.mageape.com";
    $user      = "admin";
    $pass      = "tellno1";
    $apimethod = "m2_soap";    
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Mage Ape</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</head>
<body>
<div class="container">
  <div class="row">
    <div class="col-xs-12 col-md-4 text-xs-center text-md-left order-md-last">
      <img src="Mage_ape1.png" style="width:100%;max-width:220px;"><br>
    </div>
    <div class="col-sx-12 col-md-7 offset-lg-1">
      <h1><a href="http://mageape.com/">Mage Ape</a><small> Magento&nbspAPI&nbsptest</small></h1>
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
          <div class="btn-group btn-group-toggle" data-toggle="buttons">
            <label class="btn btn-outline-primary <?php if($apimethod=="m1_soap1"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m1_soap1" id="optionA1" <?php if($apimethod=="m1_soap1"){echo"checked";}?>>Mage 1.x SOAP V1</label>
            <label class="btn btn-outline-primary <?php if($apimethod=="m1_soap2"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m1_soap2" id="optionA2" <?php if($apimethod=="m1_soap2"){echo"checked";}?>>Mage 1.x SOAP V2</label>
            <label class="btn btn-outline-primary <?php if($apimethod=="m2_soap"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m2_soap" id="optionA3" <?php if($apimethod=="m2_soap"){echo"checked";}?>>Mage 2.x SOAP</label>
            <label class="btn btn-outline-primary <?php if($apimethod=="m2_rest"){echo"active";}?>">
              <input type="radio" name="apimethod" value="m2_rest" id="optionA4" <?php if($apimethod=="m2_rest"){echo"checked";}?>>Mage 2.x REST</label>
          </div>
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text" id="btnGroupAddon">URL:</div>
            </div>
            <input type="text" class="form-control" name="website" value="<?php echo $inputurl; ?>">
            <div class="input-group-append">
              <button type="submit" class="btn btn-primary">Get Info</button>
            </div>
          </div>
          <div class="input-group">
            <div class="input-group-prepend">
              <div class="input-group-text">Username:</div>
            </div>
            <input type="text" class="form-control" name="user" value="<?php echo $user;?>">
            <div class="input-group-prepend">
              <div class="input-group-text">Password:</div>
            </div>
            <input type="password" class="form-control" name="pass" value="<?php echo $pass; ?>">
          </div>
        </div>
      </form>
      <p>
<?php

if (!empty($_POST)) {
// loading gif
    echo '<img id="loadingGif" style="position:absolute;bottom:-20px;left:46%;" src="ape-loader2.gif">';
    ob_flush();
    $starttime = microtime(true);
// Filter URL.
    $url = correctURL($inputurl);
    postMessage(INFO, "Started test using ".($userpath?"path you specified":"default path"), $url);
    ob_flush();

// check link response
    if ($apimethod == "m2_rest") { 
        $url_response = getFromHttp($url."/index.php/rest/default/schema");
    } elseif ($apimethod == "m2_soap") {
        $url_response = getFromHttp($url."/index.php/soap/default?wsdl_list");
    } else {
        $url_response = getFromHttp($url);
    }
    $msgtype      = DANGER; // if it ain't good, it's bad.
    $msg          = $url_response['head'];
    if ($url_response['code'] < 200) { // 0 - 199 Progress messages. would be wierd to get, but ok.
        $msgtype  = WARNING;
    } elseif ($url_response['code'] >=200 && $url_response['code'] < 300) { // 200 - 299
        $msgtype  = SUCCESS;
    } elseif ($url_response['code'] >=300 && $url_response['code'] < 400) { // 300 - 399 redirect
        $msgtype  = WARNING;
        $followup = getFromHttp($url_response['redirect']);
        $msg     .= "<br>&#8627;&nbsp;".$url_response['redirect'];
        $msg     .= "<br>&nbsp;&#8627;&nbsp;Returns:&nbsp;".$followup['head'];
    } // 400 and up basically everything else. either not found, forbiden, or server error.
    postMessage($msgtype, "Request returned:", $msg);
    ob_flush();

// Validate WSLD



// Try a few useful commands to gather data and show connection is working.
    try {

    if ($apimethod == "m1_soap1") {

        // set options and create SOAP client object
        $options = array('exceptions'=>true, 'trace'=>1, 'cache_wsdl' => WSDL_CACHE_NONE);
        $client  = new SoapClient($url, $options);
        // try starting session with SOAP client
        if (empty($user) || empty($pass)) {
            $session = $client->startSession();
            postMessage(INFO, "Started session without auth", "Session ID: ".$session);
        } else {
            $session = $client->login($user, $pass);
            postMessage(SUCCESS, "Login successful", "Session ID: ".$session);
        }
        ob_flush();
        // try to read core_magento.info through SOAP client 
        $result = $client->call($session, 'core_magento.info');
        print_r($result);
        $msg    = $result['magento_edition'] . " edition " . $result['magento_version'];
        postMessage(INFO, "Version:", $msg);
        ob_flush();
        // try to read resource list through SOAP client
        $msg    = "";
        $result = $client->resources($session);
        foreach ($result as $a) {
            $msg = $msg . $a['title'] . "<br>";
        }
        postMessage(INFO, "Available resources:", $msg);
        ob_flush();

    } elseif ($apimethod == "m1_soap2") {

        // set options and create SOAP client object
        $options = array('exceptions'=>true, 'trace'=>1, 'cache_wsdl' => WSDL_CACHE_NONE);
        $client  = new SoapClient($url, $options);
        // try starting session with SOAP client
        if (empty($user) || empty($pass)) {
            $session = $client->startSession();
            postMessage(INFO, "Started session without auth", "Session ID: ".$session);
        } else {
            $session = $client->login($user, $pass);
            postMessage(SUCCESS, "Login successful", "Session ID: ".$session);
        }
        ob_flush();
        // try to read core_magento.info through SOAP client 
        $result  = $client->magentoInfo($session);
        $msg     = $result->magento_edition . " edition " . $result->magento_version;
        postMessage(INFO, "Version:", $msg);
        ob_flush();
        // try to read resource list through SOAP client
        $msg    = "";
        $result = $client->resources($session);
        foreach ($result as $a) {
            $msg = $msg . $a->title . "<br>";
            #$msg = $msg . var_dump($a) . "<br>";
        }
        postMessage(INFO, "Available resources:", $msg);
        ob_flush();

    } elseif ($apimethod == "m2_soap") {
        $options = array('exceptions'=>true, 'trace'=>1, 'cache_wsdl' => WSDL_CACHE_NONE);
        // get admin token
        $client = new SoapClient($url."/index.php/soap/default?wsdl&services=integrationAdminTokenServiceV1", $options);
        $result = $client->integrationAdminTokenServiceV1CreateAdminAccessToken(['username'=>$user, 'password'=>$pass]);
	postMessage(SUCCESS, "Login successful", "Token: ".$result->result); 
        // get version
        $msg = file_get_contents($url."/magento_version");
        postMessage(INFO, "Version:", $msg);
        // try to read data through SOAP
        $options['stream_context'] = stream_context_create(['http' => ['header' => sprintf("Authorization: Bearer ".$result->result)]]);
        $client = new SoapClient($url."/index.php/soap/default?wsdl&services=storeStoreRepositoryV1", $options);
        $result = $client->storeStoreRepositoryV1GetList();
        $msg = "";
        foreach ($result->result->item as $value) {
            $msg = $msg . $value->code . "<br>";
        }
        // resource list
        $result = json_decode(file_get_contents($url."/index.php/soap/default?wsdl_list"));
	$msg = "";
	foreach ($result as $key => $value) {
	    $msg = $msg . $key . "<br>";
	}
        postMessage(INFO, "Available resources:", $msg);
        ob_flush();

    } elseif ($apimethod == "m2_rest") {
    	
        // Start of customizations
        //M2 does not require you to login to get get this information so a simple file_get_contents will work
        $response = json_decode(file_get_contents("$url/index.php/rest/default/schema"));
	$result   = $response->info;
        $msg      = $result->title . " edition " . $result->version;
        postMessage(INFO, "Version:", $msg);

        //get user info from above
        $userData = [
            "username" => $user,
            "password" => $pass
        ];

        //This is gross it should be handled with GuzzleHttp or something simular
        $ch = curl_init("$url/index.php/rest/V1/integration/admin/token");

        $options =
        [
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => json_encode($userData),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))]
        ];

        curl_setopt_array($ch, $options);
        $token = curl_exec($ch);

        $ch    = curl_init("$url/index.php/rest/V1/modules");

        $options =
        [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json", "Authorization: Bearer " . json_decode($token)]
        ];

        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        $out    = json_decode($result);
        curl_close($ch);
        ///


        $msg    = "";

        foreach ($out as $a) {

            $msg = $msg . $a . "<br>";
        }
        postMessage(INFO, "Available resources:", $msg);
        ob_flush();
    }///end of Soap2 REST customizations
    }

// Error handling
    catch(Exception $e) {
        $timestamp = round(microtime(true)-$starttime,2);
        echo '<div class="alert '.DANGER.'" role="alert">';
        echo '<div style="float:right">'.$timestamp.'s</div>';
        echo '<h4>Catch Error</h4>';
        // Profile the error
        if (is_a($e, 'SoapFault')) {
            // object type denotes SOAP error returned
            echo $e->faultstring . '<br>';
            if ($e->faultstring == 'Access denied.') {
                echo 'The user <strong>' . $user . '</strong> cannot ';
                foreach ($e->getTrace() as $traced) {	
                    $lastFunction = $traced['function'];
                    echo '-&gt;<strong>' . $lastFunction . '</strong>';

                }
                if ($lastFunction == "login") {
                    echo '<br>check username and password.';
                } else {
                    echo "<br>Have you checked the user's roll?";
                }
            }
        } else {
            echo $e->getMessage();
        }
        echo '<hr><pre><code>';
        var_dump($e);
        echo "</code></pre></div>\r\n";
    }

}
?>
      </p>
    </div>
  </div>
</div>

<script>document.getElementById('loadingGif').style.display = 'none';</script>
</body></html>
