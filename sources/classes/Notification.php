<?php
/**
 * Notification class.
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Notification class.
 * Facade for sending notifications. support for email and pushove
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class  Notification {

	/**
	 * Pushover API token
	 * @var String	Pushover API token
	 */
	private static $_pushoverToken = '10hXML7F6wL4eKnV2pP8XY9hcWULWV';



	const EMAIL = 'email_addresses';
	const PUSHOVER = 'pushover_users';
	const HTTP = 'http_addresses';


	/**
	 * Send notifications
	 *
	 * @param String $title      Display title
	 * @param String $message    Message to display
	 * @param Array  $recipients Array containing recipients per key
	 * @param Array  $raw_data   Array containing the raw data of the message.
	 *
	 * @return void
	 */
	public static function notify($title, $message, $recipients, $raw_data) {

		// send emails
		if ($email_addresses = Notification::_prepapreRecipients($recipients, self::EMAIL)) {
			foreach ($email_addresses as $email_address) {
				self::notifyEmail($title, $message, $email_address);
			}
		}

		// send Pushover notifications
		if ($user_tokens = Notification::_prepapreRecipients($recipients, self::PUSHOVER)) {
			foreach ($user_tokens as $user_token) {
				self::notifyPushover($title, $message, $user_token);
			}
		}

		// trigger http(s) notifications
		if ($urls = Notification::_prepapreRecipients($recipients, self::HTTP)) {
			foreach ($urls as $url) {
				self::notifyHttp($title, $message, $url, $raw_data);
			}
		}
	}

	/**
	 * Helper function to convert recipients into one array per type
	 *
	 * @param Array  $recipients set of recipients grouped by key
	 * @param String $type       group identifier
	 *
	 * @return array set of recipients for one type
	 */
	private static function _prepapreRecipients($recipients, $type) {
		if (isset($recipients->$type)) {
			if (!is_array($recipients->$type)) {
				$addresses = explode(';', $recipients->$type);
			} else {
				$addresses = $recipients->$type;
			}

			if (count($addresses) > 0) {
				return $addresses;
			}
		}

		// send false back if we have not found a set of recipients for this type
		return false;
	}

	/**
	 * Notify one Pushover user
	 *
	 * @param String $title      Display title
	 * @param String $message    Message to display
	 * @param String $user_token Unique user identifier
	 *
	 * @return Boolean succes
	 */
	protected static function notifyPushover($title, $message, $user_token) {
		if (empty($user_token)) {
			return false;
		}

		$push = new Pushover_API();
		$push->setToken(self::$_pushoverToken);
		$push->setUser($user_token);

		$push->setTitle($title);
		$push->setMessage($message);

		return  $push->send();
	}

	/**
	 * Notify one email address
	 *
	 * @param String $title         Display title
	 * @param String $message       Message to display
	 * @param String $email_address Unique user identifier
	 *
	 * @return Boolean succes
	 */
	protected  static function notifyEmail($title, $message, $email_address) {
		if (empty($email_address)) {
			return false;
		}

		$to_email 	= $email_address;
		$from 		= 'deploy@'.php_uname('n');
		$subject 	= $title;

		$headers  = "From: $from\r\n";
		$headers .= "Content-type: text/html\r\n";

		$message = '<pre>'.$message.'</pre>';

		// now lets send the email.
		return mail($to_email, $subject, $message, $headers);
	}

	/**
	 * Notify one http(s) address
	 *
	 * @param String $title    Display title
	 * @param String $message  Message to display
	 * @param String $url  	   Unique user identifier
	 * @param Array  $raw_data Array containing the raw data of the message.
	 *
	 * @return Boolean succes
	 */
	protected static function notifyHttp($title, $message, $url, $raw_data) {
		if (empty($url)) {
			return false;
		}

		$data = array_merge(
			$raw_data,
			array(
				'title' => $title,
				'message' => $message,
			)
		);

		// Initializing curl
		$curl_handle = curl_init($url);

		curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query($data));
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);

		$result = curl_exec($curl_handle);

		// out put result to show 404 or 500 errors
		echo $result;

		return  $result;
	}
}
