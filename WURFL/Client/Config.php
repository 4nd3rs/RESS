<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */
/**
 * Configuration class for the WurflCloud_Client
 * 
 * A usage example of WurflCloud_Client_Config:
 * <code>
 * // Create a configuration object 
 * $config = new WurflCloud_Client_Config(); 
 * // Paste your API Key below 
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * </code>
 * 
 * @package WurflCloud_Client
 */
class WurflCloud_Client_Config {
	/**
	 * The WURFL Cloud Service API type
	 * @var string
	 */
	const API_HTTP = 'http';
	/**
	 * Reserved for future use
	 * @var string
	 */
	const API_TCP = 'tcp';
	/**
	 * Use the PHP 'curl' extension
	 * @var int
	 */
	const METHOD_CURL = 'WurflCloud_HttpClient_Curl';
	/**
	 * Use straight PHP TCP calls using fsockopen().  This is the default method.
	 * @var int
	 */
	const METHOD_FSOCK = 'WurflCloud_HttpClient_Fsock';
	
	/**
	 * The timeout in milliseconds to wait for the WURFL Cloud request to complete
	 * @var int
	 */
	public $http_timeout = 1000;
	/**
	 * Enables or disables the use of compression in the WURFL Cloud response.  Using compression
	 * can increase CPU usage in very high traffic environments, but will decrease network traffic
	 * and latency.
	 * @var boolean
	 */
	public $compression = true;
	/**
	 * Force a given HTTP method
	 * @var string
	 * @see METHOD_CURL, METHOD_FSOCK
	 */
	public $http_method = self::METHOD_FSOCK;
	/**
	 * If true, the entire cache (e.g. memcache, APC) will be cleared if the WURFL Cloud Service has
	 * been updated.  This option should not be enabled for production use since it will result in a
	 * massive cache purge, which will result in higher latency lookups.
	 * @var boolean
	 */
	public $auto_purge = false;
	/**
	 * The interval in seconds that after which API will report its performance 
	 * @var int
	 * @access private
	 */
	public $report_interval = 60;
	/**
	 * The WURFL Cloud API Type to be used.  Currently, only WurflCloud_Client_Config::API_HTTP is supported.
	 * @var string
	 * @see API_HTTP
	 * @access private
	 */
	public $api_type = 'http';
	
	/**
	 * WURFL Cloud Service API Key
	 * 
	 * The API Key is used to authenticate with the WURFL Cloud Service.  It can be found at in your account
	 * at http://www.scientiamobile.com/myaccount
	 * The API Key is 39 characters in with the format: nnnnnn:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
	 * where 'n' is a number and 'x' is a letter or number
	 * 
	 * @var string
	 */
	public $api_key = null;
	/**
	 * WURFL Cloud servers to use for uncached requests.  The "weight" field can contain any positive number,
	 * the weights are relative to each other.  
	 * @var array WURFL Cloud Servers
	 */
	public $wcloud_servers = array(
	//	'nickname'   	=> array(host, weight),
		'wurfl_cloud' 	=> array('api.wurflcloud.com', 80),
	);
	
	/**
	 * The WURFL Cloud Server that is currently in use, formatted like:
	 * 'server_nickname' => array('url', 'weight')
	 * @var array
	 */
	private $current_server = array();
	
	/**
	 * Adds the specified WURFL Cloud Server
	 * @param string $nickname Unique identifier for this server
	 * @param string $url URL to this server's API
	 * @param int $weight Specifies the chances that this server will be chosen over
	 * the other servers in the pool.  This number is relative to the other servers' weights.
	 */
	public function addCloudServer($nickname, $url, $weight=100) {
		$this->wcloud_servers[$nickname] = array($url, $weight);
	}
	/**
	 * Removes the WURFL Cloud Servers
	 */
	public function clearServers() {
		$this->wcloud_servers = array();
	}
	/**
	 * Determines the WURFL Cloud Server that will be used and returns its URL.
	 * @return string WURFL Cloud Server URL
	 */
	public function getCloudHost() {
		$server = $this->getWeightedServer();
		return $server[0];
	}
	/**
	 * Uses a weighted-random algorithm to chose a server from the pool
	 * @return array Server in the form array('host', 'weight')
	 */
	public function getWeightedServer() {
		if (count($this->current_server) === 1) {
			return $this->current_server;
		}
		if (count($this->wcloud_servers) === 1) {
			return $this->wcloud_servers[key($this->wcloud_servers)];
		}
		$max = $rcount = 0;
		foreach ($this->wcloud_servers as $k => $v) {
			$max += $v[1];
		}
		$wrand = mt_rand(0, $max);
		$k = 0;
		foreach ($this->wcloud_servers as $k => $v) {
			if ($wrand <= ($rcount += $v[1])) {
				break;
			}
		}
		$this->current_server = $this->wcloud_servers[$k];
		return $this->current_server;
	}
}