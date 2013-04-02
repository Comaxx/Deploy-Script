<?php
/**
 * Base Exception class
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Base Exception class, Logs msg via NedStars_Log
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Exception extends Exception {

	/**
	 * Constructor
	 *
	 * @param String    $message  Exception msg
	 * @param Int       $code     Exception code
	 * @param Exception $previous previous exception
	 *
	 * @return void
	 */
	public function __construct($message, $code = 0, Exception $previous = null) {
		//log the message via nedstars log
		NedStars_Log::exception($message);
		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}


}