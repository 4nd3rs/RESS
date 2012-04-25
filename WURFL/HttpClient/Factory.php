<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */
/**
 * A factory class for building HTTP Clients
 */
class WurflCloud_HttpClient_Factory {
	protected static $http_clients = array(
		'WurflCloud_HttpClient_Curl',
		'WurflCloud_HttpClient_Fsock',
	);
	/**
	 * Returns an HTTP Client for use with WURFL Cloud
	 * @param string $class_name Specify a custom class for the HTTP Client
	 * @return WurflCloud_HttpClient_AbstractHttpClient
	 * @throws WurflCloud_Client_NoSupportedHttpClientException
	 */
	public static function getClient($class_name=null) {
		if ($class_name !== null && class_exists($class_name)) {
			return new $class_name();
		}
		foreach (self::$http_clients as $class_name) {
			$supported = call_user_func(array($class_name, 'isSupported'));
			if ($supported === true) {
				return new $class_name;
			}
		}
		throw new WurflCloud_Client_NoSupportedHttpClientException('No supported PHP HTTP Client could be found.');
	}
}