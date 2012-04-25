<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */
/**
 * This is the abstract class for HTTP Communications
 */
abstract class WurflCloud_HttpClient_AbstractHttpClient {
	protected $use_compression = true;
	protected $timeout_ms = 1000;
	protected $request_headers = array();
	protected $response_headers;
	protected $response_http_status;
	protected $response_body;
	protected $success;
	
	public function setTimeout($milliseconds) {
		$this->timeout_ms = $milliseconds;
	}
	
	public function setUseCompression($use_compression = true) {
		$this->use_compression = $use_compression;
	}
	
	public function addHttpRequestHeader($name, $value) {
		$this->request_headers[$name] = $value;
	}
	
	/**
	 * Adds the HTTP Header specified by $source_name (if found) in the $http_request
	 * under $dest_name.  Example: addRequestHeaderIfExists('HTTP_USER_AGENT', 'User-Agent');
	 * @param array $http_request
	 * @param string $source_name
	 * @param string $dest_name
	 * @return boolean true if the header was found and added, otherwise false
	 */
	public function addHttpRequestHeaderIfExists(array $http_request, $source_name, $dest_name) {
		if (array_key_exists($source_name, $http_request)) {
			$this->addHttpRequestHeader($dest_name, $http_request[$source_name]);
			return true;
		}
		return false;
	}
	
	public function getResponseBody() {
		return $this->response_body;
	}
	
	public function wasCalled() {
		return ($this->success !== null);
	}
	
	public function success() {
		return $this->success;
	}
	
	abstract public function call($host, $request_path, $auth_user, $auth_pass);
	
	protected function processResponse($response) {
		list($headers, $body) = explode("\r\n\r\n", $response, 2);
		$this->processResponseHeaders($headers);
		$this->processResponseBody($body);
	}
	
	protected function processResponseHeaders($headers) {
		$this->response_headers = explode("\r\n", $headers);
		$this->response_http_status = $this->response_headers[0];
		list($protocol, $http_status_code, $reason_code) = explode(' ', $this->response_http_status, 3);
		$http_status_code = (int)$http_status_code;
		if ($http_status_code >= 400 ) {
			$this->success = false;
			switch ($reason_code) {
				case 'API_KEY_INVALID':
					throw new WurflCloud_Client_ApiKeyInvalidException($this->response_http_status);
					break;
				case 'AUTHENTICATION_REQUIRED':
					throw new WurflCloud_Client_NoAuthProvidedException($this->response_http_status);
					break;
				case 'API_KEY_EXPIRED':
					throw new WurflCloud_Client_ApiKeyExpiredException($this->response_http_status);
					break;
				case 'API_KEY_REVOKED':
					throw new WurflCloud_Client_ApiKeyRevokedException($this->response_http_status);
					break;
				case 'INVALID_SIGNATURE':
					throw new WurflCloud_Client_InvalidSignatureException($this->response_http_status);
					break;
				default:
					throw new WurflCloud_Client_HttpException("The WURFL Cloud service returned an unexpected response: $this->response_http_status", $this->response_http_status);
					break;
			}
		}
		$this->success = true;
	}
	
	protected function processResponseBody($body) {
		$this->response_body = $body;
	}
}