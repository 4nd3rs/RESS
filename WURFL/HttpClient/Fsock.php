<?php
/**
 * This software is the Copyright of ScientiaMobile, Inc.
 * 
 * Please refer to the LICENSE.txt file distributed with the software for licensing information.
 * 
 * @package WurflCloud_Client
 */
/**
 * An HTTP Client that uses PHP's code fsock() functions
 */
class WurflCloud_HttpClient_Fsock extends WurflCloud_HttpClient_AbstractHttpClient {
	public static function isSupported() {
		return function_exists('fsockopen');
	}
	/**
	 * Returns the response body using fsockopen()
	 * @param string $host Hostname of the remote server
	 * @param string $request_path Request Path/URI
	 * @param string $auth_user Basic Auth Username
	 * @param string $auth_pass Basic Auth Password
	 * @throws  WurflCloud_Client_HttpException Unable to query server
	 * @author skamerman
	 */
	public function call($host, $request_path, $auth_user, $auth_pass) {
		if (strpos(':', $host) !== false) {
			list($host, $port) = explode(':', $host);
		} else {
			$port = '80';
		}
		// Open connection
		$fh = @fsockopen($host, $port, $errno, $error, ($this->timeout_ms / 1000));
		if (!$fh) {
			throw new WurflCloud_Client_HttpException("Unable to contact server: fsock Error: $error", null);
		}
		
		// Setup HTTP Request headers
		$http_header = "GET $request_path HTTP/1.1\r\n";
		$http_header.= "Host: $host\r\n";
		if ($this->use_compression === true) {
			$http_header.= "Accept-Encoding: gzip\r\n";
		}
		$http_header.= "Accept: */*\r\n";
		$http_header.= "Authorization: Basic ".base64_encode($auth_user.':'.$auth_pass)."\r\n";
		foreach ($this->request_headers as $key => $value) {
			$http_header .= "$key: $value\r\n";
		}
		$http_header.= "Connection: Close\r\n";
		$http_header.= "\r\n";
//die('<pre>'.nl2br($http_header).'</pre>');
		// Setup timeout
		stream_set_timeout($fh, 0, $this->timeout_ms * 1000);
		
		// Send Request headers
		fwrite($fh, $http_header);
		
		// Get Response
		$response = '';
		while ($line = fgets($fh)) {
			$response .= $line;
		}
		$stream_info = stream_get_meta_data($fh);
		fclose($fh);
		
		// Check for Timeout
		if ($stream_info['timed_out']) {
			throw new WurflCloud_Client_HttpException("HTTP Request timed out", null);
		}
		
		$this->processResponse($response);
	}
	
	protected function processResponseBody($body) {
		if ($this->responseIsCompressed()) {
			$this->response_body = $this->decompressBody($body);
		} else {
			$this->response_body = $body;
		}
	}
	
	protected function decompressBody($body) {
		$data = @gzinflate(substr($body, 10));
		if (!is_string($data)) {
			throw new WurflCloud_Client_HttpException("Unable to decompress the WURFL Cloud Server response", $this->response_http_status);
		}
		return $data;
	}
	
	protected function responseIsCompressed() {
		// Decompress if necessary
		foreach ($this->response_headers as $header) {
			if (stripos($header, 'Content-Encoding: gzip') !== false) {
				return true;
				break;
			}
		}
		return false;
	}
}