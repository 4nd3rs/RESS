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
 * The APC WurflCloud_Client Cache Provider
 *
 * An example of using APC for caching:
 * <code>
 * // Create Configuration object
 * $config = new WurflCloud_Client_Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use APC Caching
 * $cache = new WurflCloud_Cache_APC();
 * // Create Client
 * $client = new WurflCloud_Client($config, $cache);
 * </code>
 *
 * @throws WurflCloud_Client_Exception Required module does not exist
 * @package WurflCloud_Client
 * @subpackage Cache
 */
class WurflCloud_Cache_APC implements WurflCloud_Cache_CacheInterface {

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
	 * @var string
	 * @access private
	 */
	protected $prefix = 'dbapi_';

	public function __construct() {
		if (!function_exists('apc_store')) {
			throw new WurflCloud_Client_Exception("The 'apc' extension is not loaded.");
		}
	}

	public function getDevice($user_agent){
		$device_id = apc_fetch(md5($user_agent));
		if ($device_id !== false) {
			$caps = apc_fetch($device_id);
			if ($caps !== false) {
				$this->incrementHit();
				return $caps;
			}
		}
		$this->incrementMiss();
		return false;
	}
	public function getDeviceFromID($device_id) {
		$result = apc_fetch($device_id);
		return ($result === false)? false: $result;
	}
	public function setDevice($user_agent, $capabilities){
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		apc_add(md5($user_agent), $capabilities['id'], $ttl);
		apc_add($capabilities['id'], $capabilities, $ttl);
		return true;
	}
	public function setDeviceFromID($device_id, $capabilities){
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}

		apc_add($device_id, $capabilities, $ttl);
		return true;
	}
	public function getMtime(){
		return (int)apc_fetch($this->prefix.'mtime');
	}
	public function setMtime($server_mtime){
		return apc_store($this->prefix.'mtime',$server_mtime,0);
	}
	public function purge(){
		return apc_clear_cache('user');
	}
	public function incrementHit() {
		apc_add($this->prefix.'hit', 0);
		apc_inc($this->prefix.'hit', 1);
	}
	public function incrementMiss() {
		apc_add($this->prefix.'miss', 0);
		apc_inc($this->prefix.'miss', 1);
	}
	public function incrementError() {
		// Using Memcache::add() to prevent race if it was pushed out of memory
		apc_add($this->prefix.'error', 0);
		apc_inc($this->prefix.'error', 1);
	}
	public function setCachePrefix($prefix) {
		$this->prefix = $prefix.'_';
	}
	public function getCounters() {
		$counters = array();
		$result = apc_fetch(array($this->prefix.'hit', $this->prefix.'miss', $this->prefix.'error'));
		$counters['hit'] = array_key_exists($this->prefix.'hit', $result)? $result[$this->prefix.'hit']: 0;
		$counters['miss'] = array_key_exists($this->prefix.'miss', $result)? $result[$this->prefix.'miss']: 0;
		$counters['error'] = array_key_exists($this->prefix.'error', $result)? $result[$this->prefix.'error']: 0;
		$counters['age'] = $this->getReportAge();
		return $counters;
	}
	public function resetCounters() {
		apc_store($this->prefix.'hit', 0);
		apc_store($this->prefix.'miss', 0);
		apc_store($this->prefix.'error', 0);
	}
	public function resetReportAge() {
		apc_store($this->prefix.'reportTime', time());
	}
	public function getReportAge() {
		if ($last_time = apc_fetch($this->prefix.'reportTime')) {
			return time() - $last_time;
		}
		return 0;
	}
	public function stats(){
		return apc_cache_info('user',true);
	}
	public function close(){}
}
