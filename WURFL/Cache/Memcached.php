<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 * @subpackage Cache
 */
/**
 * The Memcached WurflCloud_Client Cache Provider
 * 
 * NOTE: PHP has two very common extensions for communicating with Memcache:
 * 'memcache' and 'memcached'.  Either will work, but you must use the correct
 * WurflCloud_Cache_CacheInterface class!
 * 
 * An example of using Memcached for caching:
 * <code>
 * // Create Configuration object
 * $config = new WurflCloud_Client_Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use Memcached Caching
 * $cache = new WurflCloud_Cache_Memcached();
 * // Set the Memcache server
 * $cache->addServer('localhost');
 * // Create Client
 * $client = new WurflCloud_Client($config, $cache);
 * </code>
 * 
 * You can also specify mutiple servers with different ports and weights:
 * <code>
 * // Create Cache object
 * $cache = new WurflCloud_Cache_Memcached();
 * // Add a local memcache server at port 11211 and weight 60
 * $cache->addServer('localhost', 11211, 60);
 * // Add another server with a weight of 40 - it will be used about 40% of the time
 * $cache->addServer('192.168.1.10', 11211, 40);
 * </code>
 * 
 * If you have unusual traffic patterns, you may want to add some randomness to your
 * cache expiration, so you don't get a bunch of entries expiring at the same time:
 * <code>
 * // Create Cache object
 * $cache = new WurflCloud_Cache_Memcached();
 * // Add up to 10 minutes (600 seconds) to the cache expiration
 * $cache->cache_expiration_rand_max = 600;
 * </code>
 * 
 * @throws WurflCloud_Client_Exception Required module does not exist
 * @package WurflCloud_Client
 * @subpackage Cache
 */
class WurflCloud_Cache_Memcached implements WurflCloud_Cache_CacheInterface {
	/**
	 * Number of seconds to keep device cached in memory.  Default: 0 - forever.
	 * Note: the device will eventually be pushed out of memory if the memcached
	 * process runs out of memory.
	 * @var Int Seconds to cache the device in memory
	 */
	public $cache_expiration = 86400;
	/**
	 * Used to add randomness to the cache expiration.  If this value is 0, no 
	 * randomness will be added, if it's above 0, a random value between 0..value
	 * will be added to the cache_expiration to prevent massive simultaneous expiry
	 * @var int
	 */
	public $cache_expiration_rand_max = 0;
	/**
	 * @var Memcached
	 */
	protected $memcache;
	/**
	 * Memcache Key prefix
	 * @var string
	 * @access private
	 */
	protected $prefix = 'dbapi_';
	/**
	 * If true, data stored in Memcache will be compressed
	 * @var boolean
	 */
	public $compression = false;
	
	public function __construct() {
		if (!class_exists('Memcached', false)) {
			throw new WurflCloud_Client_Exception("The class 'Memcached' does not exist.  Please verify the extension is loaded");
		}
		$this->memcache = new Memcached($this->prefix.'persist');
		$this->memcache->setOption(Memcached::OPT_COMPRESSION, $this->compression);
	}
	/**
	 * Add Memcache server
	 * @param string $host Server hostname
	 * @param int $port Server port number
	 * @param int $weight Probability that this server will be used, relative to other added servers
	 */
	public function addServer($host, $port = 11211, $weight = 1) {
		// TODO: validate claims that getServerList() is required to check against duplicate server entries
		$this->memcache->addServer($host, $port, $weight);
	}
	public function getDevice($user_agent){
		$device_id = $this->memcache->get(md5($user_agent));
		if ($device_id !== false) {
			$caps = $this->memcache->get($device_id);
			if ($caps !== false) {
				$this->incrementHit();
				return $caps;
			}
		}
		$this->incrementMiss();
		return false;
	}
	public function getDeviceFromID($device_id) {
		return $this->memcache->get($device_id);
	}
	public function setDevice($user_agent, $capabilities){
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		// Set user_agent => device_id
		$this->memcache->add(md5($user_agent), $capabilities['id'], $ttl);
		// Set device_id => (array)capabilities
		$this->memcache->add($capabilities['id'], $capabilities, $ttl);
		return true;
	}
	public function setDeviceFromID($device_id, $capabilities){
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		$this->memcache->add($device_id, $capabilities, $ttl);
		return true;
	}
	public function getMtime(){
		return (int)$this->memcache->get($this->prefix.'mtime');
	}
	public function setMtime($server_mtime){
		return $this->memcache->set($this->prefix.'mtime',$server_mtime,0);
	}
	public function purge(){
		return $this->memcache->flush();
	}
	public function incrementHit() {
		// Using Memcache::add() to prevent race if it was pushed out of memory
		$this->memcache->add($this->prefix.'hit', 0);
		$this->memcache->increment($this->prefix.'hit', 1);
	}
	public function incrementMiss() {
		// Using Memcache::add() to prevent race if it was pushed out of memory
		$this->memcache->add($this->prefix.'miss', 0);
		$this->memcache->increment($this->prefix.'miss', 1);
	}
	public function incrementError() {
		// Using Memcache::add() to prevent race if it was pushed out of memory
		$this->memcache->add($this->prefix.'error', 0);
		$this->memcache->increment($this->prefix.'error', 1);
	}
	public function setCachePrefix($prefix) {
		$this->prefix = $prefix.'_';
	}
	public function getCounters() {
		$counters = array();
		$result = $this->memcache->getMulti(array($this->prefix.'hit', $this->prefix.'miss', $this->prefix.'error'));
		$counters['hit'] = array_key_exists($this->prefix.'hit', $result)? $result[$this->prefix.'hit']: 0;
		$counters['miss'] = array_key_exists($this->prefix.'miss', $result)? $result[$this->prefix.'miss']: 0;
		$counters['error'] = array_key_exists($this->prefix.'error', $result)? $result[$this->prefix.'error']: 0;
		$counters['age'] = $this->getReportAge();
		return $counters;
	}
	public function resetCounters() {
		$this->memcache->set($this->prefix.'hit', 0);
		$this->memcache->set($this->prefix.'miss', 0);
		$this->memcache->set($this->prefix.'error', 0);
	}
	public function resetReportAge() {
		$this->memcache->set($this->prefix.'reportTime', time());
	}
	public function getReportAge() {
		if ($last_time = $this->memcache->get($this->prefix.'reportTime')) {
			return time() - $last_time;
		} else {
			$this->resetReportAge();
			return time();
		}
	}
	public function stats(){
		return $this->memcache->stats();
	}
	public function close(){
		$this->memcache = null;
	}
	public function &getMemcache() {
		return $this->memcache;
	}
}
