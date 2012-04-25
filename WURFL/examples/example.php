<?php
/**
 * WURFL Cloud Client - Simple example using MyWurfl
 * @package WurflCloud_Client
 * @subpackage Examples
 * 
 * This example uses the included MyWurfl class to get device capabilities.
 * If you prefer to use the WURFL Cloud Client directly, see show_capabilities.php
 * 
 * For this example to work properly, you must put your API Key in MyWurfl.php
 * and ensure that you have the following capabilities in your account:
 *  - ux_full_desktop
 *  - brand_name
 *  - model_name
 *  
 * (see below to run this example without the above capabilities)
 */
/**
 * Include the MyWurfl.php file
 */
require_once dirname(__FILE__) . '/MyWurfl.php';

try {
	// Check if the device is mobile
	if (MyWurfl::get('ux_full_desktop')) {
		echo 'This is a mobile device. <br/>';
		// If you don't have 'brand_name' and 'model_name', you can comment out this line to run the example
		echo 'Device: '.MyWurfl::get('brand_name').' '.MyWurfl::get('model_name')." <br/>\n";
	} else {
		echo "This is a desktop browser <br/>\n";
	}
} catch(Exception $e) {
	echo 'Error: '.$e->getMessage();
}
