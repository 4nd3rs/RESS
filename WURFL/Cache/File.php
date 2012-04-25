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
 * The FileSystem WurflCloud_Client Cache Provider
 *
 * An example of using the Filesystem for caching:
 * <code>
 * // Create Configuration object
 * $config = new WurflCloud_Client_Config();
 * // Set API Key
 * $config->api_key = 'xxxxxx:xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
 * // Use Filesystem Caching
 * $cache = new WurflCloud_Cache_File();
 * // Create Client
 * $client = new WurflCloud_Client($config, $cache);
 * </code>
 * 
 * You can also specify the cache directory and cache expiration:
 * <code>
 * // Create Cache object
 * $cache = new WurflCloud_Cache_File();
 * // Set Cache directory
 * $cache->cache_dir = '/tmp/';
 * // Set Cache expiration
 * $cache->cache_expiration = 86400;
 * </code>
 *
 * @package WurflCloud_Client
 * @subpackage Cache
 */
class WurflCloud_Cache_File implements WurflCloud_Cache_CacheInterface {
	
	/**
	 * The directory where the cache files will be stored.
	 * This must be writable by the webserver!
	 * Default: the data/cache/ folder
	 * @var string
	 */
	public $cache_dir;
	/**
	 * Number of seconds to keep device cached in memory.  Default: 0 - forever.
	 * Note: the device will eventually be pushed out of memory if the memcached
	 * process runs out of memory.
	 * Tip: 3600 = 1 hour, 86400 = 1 day, 604800 = 1 week, 2592000 = 1 month (30 days)
	 * @var Int Seconds to cache the device in memory
	 */
	public $cache_expiration = 604800;
	
	/**
	 * Used to add randomness to the cache expiration.  If this value is 0, no 
	 * randomness will be added, if it's above 0, a random value between 0..value
	 * will be added to the cache_expiration to prevent massive simultaneous expiry
	 * @var int
	 */
	public $cache_expiration_rand_max = 0;
	
	/**
	 * If true, use filesystem hard links to increase cache performance.  By default
	 * this is changed to false on Windows do to lack of support.  If you want to use it
	 * on Windows, see Notes here: http://php.net/manual/en/function.link.php
	 * @var boolean Whether to use hard links for caching
	 */
	public $use_links = true; 
	
	/**
	 * The number of characters of the hashed key to use for filename spreading
	 * Do not change this unless you know what you're doing
	 * @var integer
	 */
	const SPREAD_CHARS = 6;
	/**
	 * The number of characters after which to create a new directory
	 * Do not change this unless you know what you're doing
	 * @var integer
	 */
	const SPREAD_DIVISOR = 2;
	/**
	 * Directory for cached Devices
	 */
	const DIR_DEVICE = 'device';
	/**
	 * Directory for cached User Agents
	 */
	const DIR_USERAGENT = 'ua';

	public function __construct() {
		// Use filesystem hard links by default if on Linux/UNIX/Mac
		$this->use_links = (DIRECTORY_SEPARATOR === '/');
		// Default cache directory is ../data/
		$this->cache_dir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'cache'.DIRECTORY_SEPARATOR;
	}

	public function getDevice($user_agent){
		return $this->getCacheData($this->getFilepath($user_agent, self::DIR_USERAGENT));
	}
	public function getDeviceFromID($device_id) {
		return $this->getCacheData($this->getFilepath($device_id, self::DIR_DEVICE));
	}
	public function setDevice($user_agent, $capabilities){
		return $this->setCacheData($this->getFilepath($user_agent, self::DIR_USERAGENT), $capabilities, self::DIR_USERAGENT);
	}
	public function setDeviceFromID($device_id, $capabilities){
		return $this->setCacheData($this->getFilepath($device_id, self::DIR_DEVICE), $capabilities, self::DIR_DEVICE);
	}
	
	/**
	 * Returns the unserialized contents of $file or false
	 * @param string $file
	 * @return array
	 */
	protected function getCacheData($file) {
		$ttl = $this->cache_expiration;
		if ($this->cache_expiration_rand_max !== 0) {
			$ttl += mt_rand(0, $this->cache_expiration_rand_max);
		}
		$mtime = @filemtime($file);
		if ($mtime === false || (time() - $mtime) > $ttl) {
			@unlink($file);
			return false;
		}
		$raw_data = @file_get_contents($file);
		// File does not exist
		if ($raw_data === false) {
			return false;
		}
		$data = @unserialize($raw_data);
		$raw_data = null;
		// File contents cannot be unserialized
		if ($data === false || !is_array($data)) {
			@unlink($file);
			return false;
		}
		return $data;
	}
	
	/**
	 * Saves the speccified $data in the specified $file  
	 * @param string $file Full path and filename of cache file
	 * @param array $data Data to be saved
	 * @param string $type Type of file
	 * @return boolean Success
	 */
	protected function setCacheData($file, $data, $type) {
		// We need to see if the directory exists and create it if not, so we'll just attempt
		// to create the directory and assume it exists.
		@mkdir(dirname($file), 0777, true);
		if ($this->use_links === false) {
			// This will always be a user agent since caching raw devices is disabled
			return @file_put_contents($file, serialize($data));
		}
		if ($type === self::DIR_USERAGENT) {
			$device_file = $this->getFilepath($data['id'], self::DIR_DEVICE);
			// Try to create a hard link from user_agent => device.  This will return false
			// if the either the source or dest exist
			if (@link($device_file, $file) === true) {
				return true;
			} else {
				// Save device first
				@mkdir(dirname($device_file), 0777, true);
				@file_put_contents($device_file, serialize($data));
				// Now create link from user_agent => device
				@link($device_file, $file);
				return true;
			}
		} else {
			// $type must be self::DIR_DEVICE
			return @file_put_contents($file, serialize($data));
		}
	}
	
	/**
	 * Get the complete path and filename of the requested key, including the cache_path
	 * @param string $key
	 * @param string $parent_dir
	 * @return string path and filename
	 */
	protected function getFilepath($key, $parent_dir) {
		$hash = bin2hex(md5($key, true));
		return $this->cache_dir.$parent_dir.DIRECTORY_SEPARATOR.chunk_split(
			substr($hash, 0, self::SPREAD_CHARS), 
			self::SPREAD_DIVISOR, 
			DIRECTORY_SEPARATOR
		).$hash;
	}
	
	/**
	 * Returns the System Temp directory or null if it cannot be determined
	 * @return string
	 */
	public static function getSystemTempDir() {
		if (function_exists('sys_get_temp_dir')) {
			if ($dir = sys_get_temp_dir()) return self::addSlash($dir);
		}
		foreach (array('TMP', 'TEMP', 'TMPDIR') as $env_name) {
			if ($dir = getenv($env_name)) return self::addSlash($dir);
		}
		throw new WurflCloud_Client_Exception("Unable to locate System Temp directory");
	}
	
	/**
	 * Returns $path with one trailing directory separator
	 * @param string $path
	 * @return string
	 */
	public static function addSlash($path) {
		if (strlen($path) < 2) return null;
		if ($path[strlen($path)-1] === DIRECTORY_SEPARATOR) {
			return $path;
		} else {
			return $path.DIRECTORY_SEPARATOR;
		}
	}
	
	public function getMtime() {
		return 0;
	}
	public function setMtime($server_mtime) {
		return true;
	}
	public function purge(){
		return true;
	}
	public function incrementHit() {}
	public function incrementMiss() {}
	public function incrementError() {}
	public function getCounters() {
		$counters = array(
			'hit' => 0,
			'miss' => 0,
			'error' => 0,
			'age' => 0,
		);
		return $counters;
	}
	public function resetCounters() {}
	public function resetReportAge() {}
	public function getReportAge() {
		return 0;
	}
	public function stats() {
		return array();
	}
	public function close(){}
}
