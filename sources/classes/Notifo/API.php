<?php
/**
 * Notifo facade
 *
 * @project Notifo
 * @category Notifications
 * @package Notifo
 * @author  Notifo <info@notifo.com>
 * @see     https://github.com/notifo/Notifo-API-Libraries
 */

/**
 * Notifo facade
 *
 * @project Notifo
 * @category Notifications
 * @package Notifo
 * @author  Notifo <info@notifo.com>
 * @see     https://github.com/notifo/Notifo-API-Libraries
 */
class Notifo_API {
  
	const API_ROOT	= 'https://api.notifo.com/';
	const API_VER 	= 'v1';

	/**
	 * Notifo API username
	 * 
	 * @var String Notifo API username
	 */
	protected $apiUsername;
	
	/**
	 * Notifo API secret
	 * 
	 * @var String Notifo API secret 
	 */
	protected $apiSecret;

	/**
	 * API constructor
	 * 
	 * @param String $apiUsername Notifo API username
	 * @param String $apiSecret   Notifo API secret 
	 *
	 * @return void
	 */
	public function __construct($apiUsername, $apiSecret) {
		$this->apiUsername = $apiUsername;
		$this->apiSecret = $apiSecret;
	}
	
	/**
	 * Set API username
	 * 
	 * @param String $val Notifo API username
	 *
	 * @return void
	 */
	public function setApiUsername($val) {
		$this->apiUsername = $val;
	}
	
	/**
	 * Set API c
	 * 
	 * @param String $val Notifo API ApiSecret
	 *
	 * @return void
	 */
	public function setApiSecret($val) {
		$this->apiSecret = $val;
	}

	/**
	 * Send notification to Notifo
	 * 
	 * @param array $params an associative array of parameters to send to the Notifo API. These can be any of the following:  to, msg, label, title, uri
	 * 
	 * @see https://api.notifo.com/ for more information
	 *
	 * @return String Json decoded request result
	 */
	public function sendNotification($params) {
		$validFields = array('to', 'msg', 'label', 'title', 'uri');
		$params = array_intersect_key($params, array_flip($validFields));
		return $this->_sendRequest('send_notification', 'POST', $params);
	} /* end function sendNotification */

	/**
	 * Send message to Notifo
	 * 
	 * @param array $params an associative array of parameters to send to the Notifo API. These can be any of the following:  to, msg
	 * 
	 * @return String Json decoded request result
	 */
	public function sendMessage($params) {
		$validFields = array('to','msg');
		$params = array_intersect_key($params, array_flip($validFields));
		return $this->_sendRequest('send_message', 'POST', $params);
	}

	/**
	 * Subscribe a user to this services
	 * 
	 * @param String $username the username to subscribe to your Notifo service
	 *
	 * @return String Json decoded request result
	 */
	function subscribeUser($username) {
		return $this->_sendRequest('subscribe_user', 'POST', array('username' => $username));
	} /* end function subscribeUser */


	/**
	 * Helper function to send the requests
	 * 
	 * @param String $method Name of remote method to call
	 * @param String $type   HTTP method (GET, POST, etc)
	 * @param Array  $data   Array with arguments for remote method
	 *
	 * @return String Json decoded request result 
	 */
	private function _sendRequest($method, $type, $data) {
	
		$url = self::API_ROOT.self::API_VER.'/'.$method;
		
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $url);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
		if ($type == "POST") {
			curl_setopt($curl_handle, CURLOPT_POST, true);
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));
		}
		curl_setopt($curl_handle, CURLOPT_USERPWD, $this->apiUsername.':'.$this->apiSecret);
		curl_setopt($curl_handle, CURLOPT_HEADER, false);
		
		$result = curl_exec($curl_handle);
		$result = json_decode($result, true);
		return $result;
	} /* end function sendRequest */

} /* end class Notifo_API */

?>
