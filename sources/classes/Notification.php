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
 * Facade for sending notifications. support for email, notifo and pushove
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
	
	/**
	 * Notifo API user
	 * @var String Notifo API user
	 */
	private static $_notifoUser = 'ns_gitlab';
	
	/**
	 * Notifo API secret
	 * @var String Notifo API secret
	 */
	private static $_notifoSecret = '00154dc34ffbd172c4f0da5eca55f4c0d93e8fe7';
	
	const EMAIL = 'email_addresses';
	const NOTIFO = 'notifo_users';
	const PUSHOVER = 'pushover_users';
	
	/**
	 * Send notifications
	 * 
	 * @param String $title      Display title
	 * @param String $message    Message to display
	 * @param Array  $recipients Array continaing recipients per key
	 *
	 * @return void
	 */
	public static function notify($title, $message, $recipients) {

		// send emails
		if ($addresses = Notification::_prepapreRecipients($recipients, self::EMAIL)) {
			foreach ($addresses as $address) {
				self::notifyEmail($title, $message, $address);
			}
		}
		
		// send Notifo notifications
		if ($addresses = Notification::_prepapreRecipients($recipients, self::NOTIFO)) {
			foreach ($addresses as $address) {
				self::notifyNotifo($title, $message, $address);
			}
		}
		
		// send Pushover notifications
		if ($addresses = Notification::_prepapreRecipients($recipients, self::PUSHOVER)) {
			foreach ($addresses as $address) {
				self::notifyPushover($title, $message, $address);
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
	 * @param String $user_token Unique user identifyer
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
	 * Notify one Notifo user
	 * 
	 * @param String $title   Display title
	 * @param String $message Message to display
	 * @param String $user    Unique user identifyer
	 *
	 * @return Boolean succes
	 */
	protected  static function notifyNotifo($title, $message, $user) {
		if (empty($user)) {
			return false;
		}
		
		/* create a new "notifo" object */
		$notifo = new Notifo_API(self::$_notifoUser, self::$_notifoSecret);
		
		/* set the notification parameters */
		$params = array(
			"to"	=> $user, /* "to" only used with Service accounts */
			//"label"	=>"Dictionary", /* "label" only used with User accounts */
			"title"	=> $title,
			"msg"	=> $message,
			"uri"	=> ""
		);
		
		/* send the notification! */
		$result = $notifo->sendNotification($params);
		
		if (isset($result->success)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Notify one email address
	 * 
	 * @param String $title         Display title
	 * @param String $message       Message to display
	 * @param String $email_address Unique user identifyer
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
}