<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */
/**
 * Manually load exceptions in case of a autoload failure
 */
include_once dirname(__FILE__) . '/Exception.php';
/**
 * Registers the class autoloader
 */
spl_autoload_register(array('WurflCloud_Client_Client', 'loadClass'));
/**
 * WURFL Cloud Client for PHP.
 * @package WurflCloud_Client
 */
class WurflCloud_Client_Client {
	/**
	 * @var integer Configuration error
	 */
	const ERROR_CONFIG = 1;
	/**
	 * @var integer Unable to contact server or Invalid server address
	 */
	const ERROR_NO_SERVER = 2;
	/**
	 * @var integer Timed out while contacting server
	 */
	const ERROR_TIMEOUT = 4;
	/**
	 * @var integer Unable to parse response
	 */
	const ERROR_BAD_RESPONSE = 8;
	/**
	 * @var integer API Authentication failed
	 */
	const ERROR_AUTH = 16;
	/**
	 * @var integer API Key is disabled or revoked
	 */
	const ERROR_KEY_DISABLED = 32;
	/**
	 * @var string No detection was performed
	 */
	const SOURCE_NONE = 'none';
	/**
	 * @var string Response was returned from cloud
	 */
	const SOURCE_CLOUD = 'cloud';
	/**
	 * @var string Response was returned from cache
	 */
	const SOURCE_CACHE = 'cache';
	/**
	 * Flat capabilities array containing 'key'=>'value' pairs.
	 * Since it is 'flattened', there are no groups in this array, just individual capabilities.
	 * @var array
	 */
	public $capabilities = array();
	/**
	 * Errors that were returned in the response body
	 * @var array
	 */
	private $errors = array();
	/**
	 * The capabilities that will be searched for
	 * @var array
	 */
	private $search_capabilities = array();
	/**
	 * The HTTP Headers that will be examined to find the best User Agent, if one is not specified
	 * @var array
	 */
	private $user_agent_headers = array(
		'HTTP_X_DEVICE_USER_AGENT',
		'HTTP_X_ORIGINAL_USER_AGENT',
		'HTTP_X_OPERAMINI_PHONE_UA',
		'HTTP_X_SKYFIRE_PHONE',
		'HTTP_X_BOLT_PHONE_UA',
		'HTTP_USER_AGENT'
	);
	/**
	 * The HTTP User-Agent that is being evaluated
	 * @var string
	 */
	private $user_agent;
	/**
	 * The HTTP Request that is being evaluated
	 * @var array
	 */
	private $http_request;
	/**
	 * The WURFL Cloud Server that will be used to request device information (e.x. 'api.wurflcloud.com')
	 * @var string
	 */
	private $wcloud_host;
	/**
	 * The request path to the WURFL Cloud Server (e.x. '/v1/json/search:(is_wireless_device)' )
	 * @var string Request path (must begin with '/')
	 */
	private $request_path;
	/**
	 * The raw json response from the server
	 * @var string
	 */
	private $json;
	/**
	 * Storage for report data (cache hits, misses, errors)
	 * @var array
	 */
	private $report_data = array();
	/**
	 * The version of this client
	 * @var string
	 */
	private $client_version = '1.0.2';
	/**
	 * The version of the WURFL Cloud Server
	 * @var string
	 */
	private $api_version;
	/**
	 * The API Username
	 * @var integer 6-digit API Username
	 */
	private $api_username;
	/**
	 * The API Password
	 * @var string 32-character API Password
	 */
	private $api_password;
	/**
	 * The date that the WURFL Cloud Server's data was updated
	 * @var int
	 */
	private $loaded_date;
	/**
	 * Client configuration object
	 * @var WurflCloud_Client_Config
	 */
	private $config;
	/**
	 * Client cache object
	 * @var WurflCloud_Cache_CacheInterface
	 */
	private $cache;
	/**
	 * The source of the last detection
	 * @var string
	 */
	private $source;
	/**
	 * The HTTP Client that will be used to call WURFL Cloud
	 * @var WurflCloud_HttpClient_AbstractHttpClient
	 */
	private $http_client;
	
	/**
	 * Creates a new WurflCloud_Client instance
	 * @param WurflCloud_Client_Config $config Client configuration object
	 * @param WurflCloud_Cache_CacheInterface $cache Client caching object
	 * @throws WurflCloud_Client_ConfigException Invalid configuration
	 * @see WurflCloud_Client_Config
	 * @see WurflCloud_Cache_APC
	 * @see WurflCloud_Cache_Memcache
	 * @see WurflCloud_Cache_Memcached
	 * @see WurflCloud_Cache_File
	 * @see WurflCloud_Cache_Null
	 */
	public function __construct(WurflCloud_Client_Config $config, $cache=null) {
		$this->config = $config;
		$this->cache = ($cache instanceof WurflCloud_Cache_CacheInterface)? $cache: new WurflCloud_Cache_Cookie();
		$this->wcloud_host = $this->config->getCloudHost();
		$this->http_client = WurflCloud_HttpClient_Factory::getClient($this->config->http_method);
	}
	/**
	 * Get the requested capabilities from the WURFL Cloud for the given HTTP Request (normally $_SERVER)
	 * @param array $http_request HTTP Request of the device being detected
	 * @param array $search_capabilities Array of capabilities that you would like to retrieve
	 */
	public function detectDevice($http_request=null, $search_capabilities=null) {
		$this->source = self::SOURCE_NONE;
		$this->http_request = ($http_request === null)? $_SERVER: $http_request;
		$this->search_capabilities = ($search_capabilities === null)? array(): $search_capabilities;
		$this->user_agent = $this->getUserAgent($http_request);
		$result = $this->cache->getDevice($this->user_agent);
		if (!$result) {
			$this->source = self::SOURCE_CLOUD;
			$this->callWurflCloud();
			$this->validateCache();
			if ($this->source == self::SOURCE_CLOUD) {
				$this->cache->setDevice($this->user_agent, $this->capabilities);
			}
		} else {
			$this->source = self::SOURCE_CACHE;
			$this->capabilities = $result;
			// The user requested capabilities that don't exist in the cached copy.  Retrieve and cache the missing capabilities
			if (!$this->allCapabilitiesPresent()) {
				$this->source = self::SOURCE_CLOUD;
				$initial_capabilities = $this->capabilities;
				$this->callWurflCloud();
				$this->capabilities = array_merge($this->capabilities, $initial_capabilities);
				if ($this->source == self::SOURCE_CLOUD) {
					$this->cache->setDevice($this->user_agent, $this->capabilities);
				}
			}
		}
	}
	/**
	 * Gets the source of the result.  Possible values:
	 *  - cache:  from local cache
	 *  - cloud:  from WURFL Cloud Service
	 *  - none:   no detection was performed
	 *  @return string 'cache', 'cloud' or 'none'
	 */
	public function getSource() {
		return $this->source;
	}

	/**
	 * Initializes the WURFL Cloud request
	 */
	private function initializeRequest() {
		$this->splitApiKey();
		
		// If the reportInterval is enabled and past the report age, include the report data in the next request
		if ($this->config->report_interval > 0 && $this->cache->getReportAge() >= $this->config->report_interval) {
			$this->addReportDataToRequest();
			$this->cache->resetReportAge();
			$this->cache->resetCounters();
		}
		
		// Add HTTP Headers to pending request
		$this->http_client->addHttpRequestHeader('User-Agent', $this->user_agent);
		$this->http_client->addHttpRequestHeader('X-Cloud-Client', 'WurflCloudClient/PHP_'.$this->client_version);
		// Add X-Forwarded-For
		$ip = isset($this->http_request['REMOTE_ADDR'])? $this->http_request['REMOTE_ADDR']: null;
		$fwd = isset($this->http_request['HTTP_X_FORWARDED_FOR'])? $this->http_request['HTTP_X_FORWARDED_FOR']: null;
		if ($ip && $fwd) {
			$this->http_client->addHttpRequestHeader('X-Forwarded-For', $ip.', '.$fwd);
		} else if ($ip) {
			$this->http_client->addHttpRequestHeader('X-Forwarded-For', $ip);
		}
		// We use 'X-Accept' so it doesn't stomp on our deflate/gzip header
		$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_ACCEPT', 'X-Accept');
		if (!$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_X_WAP_PROFILE', 'X-Wap-Profile')) {
			$this->http_client->addHttpRequestHeaderIfExists($this->http_request, 'HTTP_PROFILE', 'X-Wap-Profile');
		}
		if (count($this->search_capabilities) === 0) {
			$this->request_path = '/v1/json/';
		} else {
			$this->request_path = '/v1/json/search:('.implode(',', $this->search_capabilities).')';
		}
	}
	/**
	 * Get the date that the WURFL Cloud Server was last updated.  This will be null if there
	 * has not been a recent query to the server, or if the cached value was pushed out of memory  
	 * @return int UNIX timestamp (seconds since Epoch)
	 */
	public function getLoadedDate() {
		if ($this->loaded_date === null){
			$this->loaded_date = $this->cache->getMtime();
		}
		return $this->loaded_date;
	}
	
	/**
	 * Returns true if all of the search_capabilities are present in the capabilities
	 * array that was returned from the WURFL Cloud Server
	 * @return boolean
	 * @see WurflCloud_Client::capabilities
	 */
	private function allCapabilitiesPresent() {
		foreach ($this->search_capabilities as $key) {
			if (!array_key_exists($key, $this->capabilities)) {
				return false;
			}
		}
		return true;
	}
	/**
	 * Retrieves the report data from the cache provider and adds it to the request
	 * parameters to be included with the next request.
	 */
	private function addReportDataToRequest() {
		$this->report_data = $this->cache->getCounters();
		$counters = array();
		foreach ($this->report_data as $key => $value) {
			$counters[] = "$key:$value";
		}
		$this->http_client->addHttpRequestHeader('X-Cloud-Counters', implode(',', $counters));
		$this->cache->resetCounters();
	}
	/**
	 * Checks if local cache is still valid based on the date that the WURFL Cloud Server
	 * was last updated.  If auto_purge is enabled, this method will clear the cache provider
	 * if the cache is outdated.
	 * @see WurflCloud_Client_Config::auto_purge
	 */
	private function validateCache() {
		$cache_mtime = $this->cache->getMtime();
		if (!$cache_mtime || $cache_mtime != $this->loaded_date) {
			$this->cache->setMtime($this->loaded_date);
			if ($this->config->auto_purge) {
				$this->cache->purge();
			}
		}
	}
	
	/**
	 * Returns the value of the requested capability.  If the capability does not exist, returns null.
	 * @param string $capability The WURFL capability (e.g. "is_wireless_device")
	 * @return mixed Value of requested $capability or null if not found
	 * @throws WurflCloud_Client_InvalidCapabilityException The requested capability is invalid or unavailable
	 */
	public function getDeviceCapability($capability) {
		$capability = strtolower($capability);
		if (array_key_exists($capability, $this->capabilities)) {
			return $this->capabilities[$capability];
		} else {
			if (!$this->http_client->wasCalled()) {
				// The capability is not in the cache (http_client was not called) - query the Cloud
				// to see if we even have the capability
				$this->source = 'cloud';
				$this->callWurflCloud();
				$this->validateCache();
				if ($this->source == 'cloud') {
					$this->cache->setDevice($this->user_agent, $this->capabilities);
				}
				if (array_key_exists($capability, $this->capabilities)) {
					return $this->capabilities[$capability];
				}
			}
			// The Cloud was queried and did not return a result for the this capability
			throw new WurflCloud_Client_InvalidCapabilityException('The requested capability ('.$capability.') is invalid or you are not subscribed to it.');
		}
	}
	/**
	 * Get the version of the WURFL Cloud Client (this file)
	 * @return string
	 */
	public function getClientVersion() {
		return $this->client_version;
	}
	/**
	 * Get the version of the WURFL Cloud Server.  This is only available
	 * after a query has been made since it is returned in the response.
	 * @return string
	 */
	public function getAPIVersion() {
		return $this->api_version;
	}
	/**
	 * Returns the Cloud server that was used
	 * @return string
	 */
	public function getCloudServer() {
		return $this->wcloud_host;
	}
	/**
	 * Make the webservice call to the server using the GET method and load the response.
	 * @throws  WurflCloud_Client_HttpException Unable to process server response
	 */
	private function callWurflCloud() {
		$this->initializeRequest();
		$this->http_client->call($this->wcloud_host, $this->request_path, $this->api_username, $this->api_password);
		$this->json = @json_decode($this->http_client->getResponseBody(), true);
		if ($this->json === null) {
			$msg = 'Unable to parse JSON response from server.';
			throw new  WurflCloud_Client_HttpException($msg, self::ERROR_BAD_RESPONSE);
		}
		$this->processResponse();
		unset($data);
	}
	/**
	 * Parses the response into the capabilities array
	 */
	private function processResponse() {
		$this->errors = $this->json['errors'];
		$this->api_version = isset($this->json['apiVersion'])? $this->json['apiVersion']: '';
		$this->loaded_date = isset($this->json['mtime'])? $this->json['mtime']: '';
		$this->capabilities['id'] = isset($this->json['id'])? $this->json['id']: '';
		$this->capabilities = array_merge($this->capabilities, $this->json['capabilities']);
	}
	/**
	 * Casts strings into PHP native variable types, i.e. 'true' into true
	 * @param string $value
	 * @return string|int|boolean|float
	 */
	private static function niceCast($value) {
		// Clean Boolean values
		if ($value === 'true') {
			$value = true;
		} else if ($value === 'false') {
			$value = false;
		} else {
			// Clean Numeric values by loosely comparing the (float) to the (string)
			$numval = (float)$value;
			if(strcmp($value,$numval)==0)$value=$numval;
		}
		return $value;
	}
	/**
	 * Return the requesting client's User Agent
	 * @param $source
	 * @return string
	 */
	private function getUserAgent($source=null) {
		if (is_null($source) || !is_array($source)) {
			$source = $_SERVER;
		}
		$user_agent = '';
		if (isset($_GET['UA'])) {
			$user_agent = $_GET['UA'];
		} else {
			foreach ($this->user_agent_headers as $header) {
				if (array_key_exists($header, $source) && $source[$header]) {
					$user_agent = $source[$header];
					break;
				}
			}
		}
		if (strlen($user_agent) > 255) {
			return substr($user_agent, 0, 255);
		}
		return $user_agent;
	}
	
	/**
	 * Splits the API Key into a username and password
	 * @return boolean success
	 */
	private function splitApiKey() {
		if (strlen($this->config->api_key) !== 39 || strpos($this->config->api_key, ':') !== 6) {
			throw new WurflCloud_Client_ConfigException('The API Key provided is invalid');
		}
		$s_user = substr($this->config->api_key, 0, 6);
		$this->api_username = (int)$s_user; 
		// Cast back to string to see if the number is the same (string)(int)00001 === '1', not '00001'
		if((string)$this->api_username !== $s_user) {
			throw new WurflCloud_Client_ConfigException('The API Key provided is invalid');
		}
		$this->api_password = substr($this->config->api_key, 7);
	}

	/**
	 * @var string The directory that this file is in.  Used by loadClass()	
	 */
	private static $base_path;
	const CLASS_PREFIX = 'WurflCloud_';
	
	/**
	 * Loads Class files
	 * @param string $class_name
	 * @access private
	 */
	public static function loadClass($class_name) {
		if (self::$base_path === null) {
			self::$base_path = dirname(dirname(__FILE__));
		}
		if (strpos($class_name, self::CLASS_PREFIX) !== 0) {
			return;
		}
		$file = str_replace('_', DIRECTORY_SEPARATOR, substr($class_name, strlen(self::CLASS_PREFIX))).'.php';
		include self::$base_path.DIRECTORY_SEPARATOR.$file;
	}
}