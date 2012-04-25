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
 * The Null WurflCloud_Client Cache Provider.  This exists only to disable caching and
 * should not be used for production installations
 * @package WurflCloud_Client
 * @subpackage Cache
 */
class WurflCloud_Cache_Null implements WurflCloud_Cache_CacheInterface {

	public $cache_expiration = 0;
	public $cache_expiration_rand_max = 0;
	public function getDevice($user_agent) {
		return false;
	}
	public function getDeviceFromID($device_id) {
		return false;
	}
	public function setDevice($user_agent, $capabilities) {
		return true;
	}
	public function setDeviceFromID($device_id, $capabilities) {
		return true;
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
