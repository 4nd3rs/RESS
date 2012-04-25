<?php
/**
 * WURFL Cloud Client - Simple example using MyWurfl
 * @package WurflCloud_Client
 * @subpackage Examples
 * 
 * This example uses the included MyWurfl class to get device capabilities.
 * If you prefer to use the WURFL Cloud Client directly, see show_capabilities.php
 * 
 * For this example to work properly, you must put your API Key in the script below.
 */
/**
 * Include the WURFL Cloud Client file
 */
require_once dirname(__FILE__) . '/../Client/Client.php';

try {
	// Create a WURFL Cloud Config
	$config = new WurflCloud_Client_Config();
	
	// Set your API Key here
	$config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
	
	// Create a WURFL Cloud Client
	$client = new WurflCloud_Client_Client($config, new WurflCloud_Cache_Null());
	
	// Detect the visitor's device
	$client->detectDevice();

	// Show all the capabilities returned by the WURFL Cloud Service
	foreach ($client->capabilities as $name => $value) {
		echo "<strong>$name</strong>: ".(is_bool($value)? var_export($value, true): $value) ."<br/>";
	}
} catch (Exception $e) {
	// Show any errors
	echo "Error: ".$e->getMessage();
}
