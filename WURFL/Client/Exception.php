<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */

class WurflCloud_Client_Exception extends Exception {}
class WurflCloud_Client_ConfigException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_InvalidCookieException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_InvalidStateException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_FileWriteException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_NoSupportedHttpClientException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_InvalidCapabilityException extends WurflCloud_Client_Exception {}
class WurflCloud_Client_HttpException extends WurflCloud_Client_Exception {
	protected $default_message;
	protected $http_status;
	public function __construct($message, $http_status) {
		$this->http_status = $http_status;
		parent::__construct($message);
	}
	public function getHttpStatus() {
		return $this->http_status;
	}
	public function getHttpStatusCode() {
		if ($this->http_status === null) {
			return 0;
		}
		list($protocol, $http_status_code, $reason_code) = explode(' ', $this->http_status, 3);
		return (int)$http_status_code;
	}
}
class WurflCloud_Client_AuthException extends WurflCloud_Client_HttpException {}
class WurflCloud_Client_ApiKeyInvalidException extends WurflCloud_Client_AuthException {
	public function __construct($http_status) {
		parent::__construct('API Authentication error, check your API Key', $http_status);
	}
}
class WurflCloud_Client_NoAuthProvidedException extends WurflCloud_Client_AuthException {
	public function __construct($http_status) {
		parent::__construct('API Authentication error, check your API Key', $http_status);
	}
}
class WurflCloud_Client_ApiKeyExpiredException extends WurflCloud_Client_AuthException {
	public function __construct($http_status) {
		parent::__construct('API Authorization error, your WURFL Cloud subscription is expired', $http_status);
	}
}
class WurflCloud_Client_ApiKeyRevokedException extends WurflCloud_Client_AuthException {
	public function __construct($http_status) {
		parent::__construct('API Authorization error, your WURFL Cloud subscription is revoked', $http_status);
	}
}
class WurflCloud_Client_InvalidSignatureException extends WurflCloud_Client_AuthException {
	public function __construct($http_status) {
		parent::__construct('API Authentication error, your request signature is invalid', $http_status);
	}
}