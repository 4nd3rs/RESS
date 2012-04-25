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
 * Cookie cache provider
 * @package WurflCloud_Client
 * @subpackage Cache
 */
class WurflCloud_Cache_Cookie implements WurflCloud_Cache_CacheInterface {
	public $cookie_name = 'WurflCloud_Client';
	public $cache_expiration = 86400;
	private $cookie_sent = false;
	
	public function getDevice($user_agent) {
		if (!isset($_COOKIE[$this->cookie_name])) {
			return false;
		}
		$cookie_data = @json_decode($_COOKIE[$this->cookie_name], true, 5);
		if (!is_array($cookie_data) || empty($cookie_data)) {
			return false;
		}
		if (!isset($cookie_data['date_set']) || ((int)$cookie_data['date_set'] + $this->cache_expiration) < time()) {
			return false;
		}
		if (!isset($cookie_data['capabilities']) || !is_array($cookie_data['capabilities']) || empty($cookie_data['capabilities'])) {
			return false;
		}
		return $cookie_data['capabilities'];
	}
	
	public function getDeviceFromID($device_id) {
		return false;
	}
	
	public function setDevice($user_agent, $capabilities) {
		if ($this->cookie_sent === true) return;
		$cookie_data = array(
			'date_set' => time(),
			'capabilities' => $capabilities,
		);
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			setcookie($this->cookie_name, json_encode($cookie_data, JSON_FORCE_OBJECT), $cookie_data['date_set'] + $this->cache_expiration);
		} else {
			setcookie($this->cookie_name, json_encode($cookie_data), $cookie_data['date_set'] + $this->cache_expiration);
		}
		$this->cookie_sent = true;
	}
	
	// Required by interface but not used for this provider
	public function setDeviceFromID($device_id, $capabilities) {return true;}
	public function getMtime() {return 0;}
	public function setMtime($server_mtime) {return true;}
	public function purge(){return true;}
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
	public function getReportAge() {return 0;}
	public function stats() {return array();}
	public function close() {}
}