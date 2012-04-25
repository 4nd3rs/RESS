<?php
/**
 * This is a test script to ensure your API Key is working properly, and that
 * you are able to access the WURFL Cloud Service.
 * 
 * If you move this file, you will need to change this to the directory where the
 * WurflCloudCient.php file is located *including a trailing slash*
 * example:
 *    $include_dir = "/usr/share/WurflCloud_Client/";
 * @package WurflCloud_Client
 * @subpackage Test
 */
/**
 * Include required files
 */
$include_dir = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Client'.DIRECTORY_SEPARATOR;

error_reporting(E_ERROR);
function shutdown_handler() {
	$error = error_get_last();
	if ($error === null || $error['type'] & ~(E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR)) return;
	echo "The WURFL Cloud Test script was unable to run on your system<br/>\nError on line {$error['line']}: {$error['message']}<br/>\n";
	echo "Please report this on the <a href=\"http://www.scientiamobile.com/forum\">ScientiaMobile Forums</a><br/>\n";
}
if (function_exists('error_get_last')) {
	register_shutdown_function('shutdown_handler');
}

$include_dir = realpath($include_dir).DIRECTORY_SEPARATOR;
$filename = $include_dir.'Client.php';
if (is_readable($filename)) {
	@include_once $filename;
	$check_classes = array('WurflCloud_Cache_Cookie','WurflCloud_Client_Client','WurflCloud_Client_Config');
	if (!interface_exists('WurflCloud_Cache_CacheInterface', true)) {
		die("Error: The file WurflCloud_Client.php was found, but required interface could not be found: WurflCloud_Cache_CacheInterface");
	}
	foreach($check_classes as $class) {
		if (!class_exists($class, true)) {
			die("Error: The file WurflCloud_Client.php was found, but some or all of the required classes could not be found: ".implode(', ',$check_classes));
		}
	}
} else {
	die("Error: Could not find WurflCloud/Client/Client.php in $include_dir.  Please edit ".__FILE__." and set \$include_dir to the directory that contains Client.php");
}
$api_key = (isset($_POST['api_key']))? $_POST['api_key']: 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';

// Text
$text = array(
	'php' => array(
		'full' => 'You version of PHP ('.PHP_VERSION.') is compatible with WURFL Cloud.',
		'partial' => '',
		'none' => 'WURFL Cloud requires at least PHP 5.1.0 and you are running '.PHP_VERSION.'.',
	),
	'cloud' => array(
		'full' => 'Your server is able to access WURFL Cloud and your API Key was accepted.',
		'partial' => 'Your server is able to access WURFL Cloud, but your API Key was not provided, so it could not be tested.',
		'none' => 'Your server is not able to access WURFL Cloud.  It\'s possible that your DNS server is slow to respond.  Please refresh
			this page to verify.',
	),
);

// Code Sample
$code_sample = '<?php
// Include the WURFL Cloud Client
// You\'ll need to edit this path
require_once \'../Client/Client.php\';
// Create a configuration object 
$config = new WurflCloud_Client_Config(); 
// Set your WURFL Cloud API Key 
$config->api_key = \''.$api_key.'\';  
// Create the WURFL Cloud Client 
$client = new WurflCloud_Client_Client($config); 
// Detect your device 
$client->detectDevice(); 
// Use the capabilities 
if ($client->getDeviceCapability(\'is_wireless_device\')) { 
    echo "This is a mobile device"; 
} else { 
    echo "This is a desktop device"; 
}
?>';

// Test PHP
$php_class = version_compare(PHP_VERSION, '5.1.0', '>=')? 'full': 'none';
$run_test = true;
if (!function_exists('json_decode')) {
	$run_test = false;
	$cloud_class = 'none';
	$text['cloud']['none'] = 'You are unable to test the WURFL Cloud Service from this server because it lacks support for JSON.';
	if ($php_class == 'full') {
		$text['php']['partial'] = 'Your PHP version is OK, but you do not have support for JSON decoding.  You will need the "json" extension to use WURFL Cloud.';
		$php_class = 'partial';
	}
}

if ($run_test) {
	// Test WURFL Cloud
	$cloud_class = 'none';
	try {
		$config = new WurflCloud_Client_Config();
		$config->http_timeout *= 2;
		$config->api_key = $api_key;
		$cache = new WurflCloud_Cache_Null();
		$client = new WurflCloud_Client_Client($config, $cache);
		$client->detectDevice($_SERVER, array('brand_name', 'model_name'));
		$cloud_class = 'full';
	} catch (WurflCloud_Client_NoAuthProvidedException $e) {
		$cloud_class = 'partial';
		$text['cloud'][$cloud_class] = 'Your server is able to access WURFL Cloud, but your API Key was not provided 
			so it could not be tested.';
	} catch (WurflCloud_Client_ApiKeyInvalidException $e) {
		$text['cloud'][$cloud_class] = 'Your server is able to access WURFL Cloud, but your API Key was rejected.  
			Please verify your key and try again.';
	} catch (WurflCloud_Client_AuthException $e) {
		$text['cloud'][$cloud_class] = get_class($e).': Your server is able to access WURFL Cloud and your API Key is correct,  
			but it is expired or has been revoked.  Please contact <a href="http://www.scientiamobile.com/" target="_blank">ScientiaMobile</a>
			to get your API Key reinstated.  Possible causes for this error: Your WURFL Cloud account is past due; Your API Key
			has been revoked due to abuse';
	} catch(WurflCloud_Client_ConfigException $e) {
		$cloud_class = 'none';
		$text['cloud'][$cloud_class] = 'There is a configuration error that is preventing the WURFL Cloud Client from 
			working: <br/><strong>'.$e->getMessage().'</strong>';
	} catch (WurflCloud_Client_HttpException $e) {
		$cloud_class = 'none';
	} catch (Exception $e) {
		// Other exception
		$cloud_class = 'none';
		$text['cloud'][$cloud_class] = 'The WURFL Cloud Client threw and unexpected exception: '.$e->getMessage();
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>WURFL Cloud Client Test Script</title>
<style type="text/css">
body, th, td, p { font-size: 12pt; font-family: Calibri, Helvetica, Verdana, sans-serif; }
h1, h2, h3, h4, h5, h6 { margin: 5px 0px 5px 0px; }
.code { text-align: left; border: 1px solid #CCC; padding: 5px; width: 708px; }
.full, .partial, .none { padding: 5px; margin: 5px 0px 5px 0px; width: 700px; text-align: left; }
.full{ border-left: 10px solid #0C0; background-color: #D9FBD2; }
.partial{ border-left: 10px solid #F93; background-color: #FFE7CE; }
.none{ border-left: 10px solid #900; background-color: #FFEAEA; }
.icode { font-family: monospace; background-color: #EEE; padding: 0px 5px 0px 5px; }
code { font-size: 10pt; } 
#logo { width: 500px; height: 60px; margin-left: 100px; 
	background-image: url(data:image/gif;base64,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); }
</style>
</head>
<body>
<div style="margin-left: auto; margin-right: auto; width: 700px; text-align: center">
 <div id="logo">&nbsp;</div>
 <br/>
 <img src="http://www.scientiamobile.com/images/logos/wurfl_logo_word.gif" align="left" height="80">
 <h1>WURFL Cloud Client by <a href="http://www.scientiamobile.com/" target="_blank">ScientiaMobile</a></h1>
 <h2>Compatibility Test Script</h2>
 <div class="code"><?php highlight_string($code_sample); ?></div>
 <div class="<?php echo $php_class; ?>">
 <h3>PHP</h3>
<p><?php echo $text['php'][$php_class]; ?></p>
</div>
 <div class="<?php echo $cloud_class; ?>">
 <h3>WURFL Cloud Service</h3>
<p><?php echo $text['cloud'][$cloud_class]; ?><br/>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<input type="text" size="45" maxlength="39" name="api_key" value="<?php echo $api_key; ?>"/>
<input type="submit" value="Test API Key"/>
</form>
</p>
</div>
<p>&nbsp;</p>
</div>
</body>
</html>
