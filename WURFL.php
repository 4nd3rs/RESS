<?php
// Include the WURFL Cloud Client
require_once 'WURFL/Client/Client.php';
// Create a configuration object
$config = new WurflCloud_Client_Config();
// Set your WURFL Cloud API Key
$config->api_key = '425902:roc8EOQzi9eIFDWx3fwVbAlhGjXay7HL';
// Create the WURFL Cloud Client
$client = new WurflCloud_Client_Client($config);
// Detect your device
$client->detectDevice();
?>