<?php
// Include the WURFL Cloud Client
require_once 'WURFL/Client/Client.php';
// Create a configuration object
$config = new WurflCloud_Client_Config();
// Set your WURFL Cloud API Key (add your own key)
//$config->api_key = '12345:abcdefgabcdefgabcdefgabcdefg';

//This file contains my own API key and is not in the Git repo
include "fragments/WURFLKey.php";
// Create the WURFL Cloud Client
$client = new WurflCloud_Client_Client($config);
// Detect your device
$client->detectDevice();
?>