<?php
/**
 * Loging class, write loging to file and or display depening level
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Loging class, write loging to file and or display depening level
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Log {

	const LVL_EXCEPTION	= 'Exception';
	const LVL_WARNING 	= 'Warning';
	const LVL_MESSAGE	= 'Message';
	const LVL_DEBUG		= 'Debug';

	/**
	 * Log level to witch level should messages be displayed, high to low -> exception, warning, message, debug
	 *
	 * @var String
	 */
	private static $_log_level = self::LVL_MESSAGE;

	/**
	 * Relative or absolute Path of log file. default is log.txt
	 *
	 * @var String
	 */
	private static $_log_file_path = 'log.txt';

	/**
	 * Module name (if system uses modues in log)
	 *
	 * @var String
	 */
	private static $_module = null;

	/**
	 * Output resource
	 *
	 * @var Resource
	 */
	private static $_stdOut = null;

	/**
	 * Sets the name of the current module that uses the log
	 *
	 * @param String $module module name
	 *
	 * @return void
	 */
	public static function setModule($module) {
		self::$_module = $module;
	}

	/**
	 * Set path to the log file
	 *
	 * @param String $log_file_path absolute path to file
	 *
	 * @return void
	 */
	public static function setLogFile($log_file_path) {
		if (empty($log_file_path)) {
			throw new NedStars_LogException('Log_file_path setting cannot be empty.', NedStars_LogException::EMPTY_PATH);
		}

		// close resource if a resource is allready loaded
		if (is_resource(self::$_stdOut)) {
			fclose(self::$_stdOut);
		}

		// set new log file location
		self::$_log_file_path = $log_file_path;
	}

	/**
	 * Set log level
	 *
	 * @param String $log_level valid log level, e.g. "Exception", "Warning", "Message", "Debug", null
	 *
	 * @exception Exception when log level is not valid
	 * @return void
	 */
	public static function setLogLevel($log_level) {
		if (!self::_isLogLevel($log_level)) {
			throw new NedStars_LogException('Log level "'.$log_level.'" not found use: "Exception", "Warning", "Message", "Debug", null', NedStars_LogException::INVALID_LEVEL);
		}

		// set log level
		self::$_log_level = $log_level;
	}


	/**
	 * Check is provided string is a valid dislay level
	 *
	 * @param String $log_level string to compare to available log levels
	 *
	 * @return Boolean
	 */
	private static function _isLogLevel($log_level) {
		// check if provided debug level is ok
		if (!in_array($log_level, array(self::LVL_EXCEPTION, self::LVL_WARNING, self::LVL_MESSAGE, self::LVL_DEBUG, null))) {
			return false;
		}

		return true;
	}


	/**
	 * Write log message to file
	 * Show log message depanding on log level
	 *
	 * @param String $line log message
	 * @param String $type type of message
	 *
	 * @return void
	 */
	private static function _writeMessage($line, $type) {
		// check for line ending
		if ( "\n" != substr($line, -1, 1)) {
			$line .= "\n";
		}

		// if log level is ok. echo message
		if (self::_shouldBeLogged($type)) {
			$log_line = date('Y-m-d H:i:s').' '.$type. ' ';
			if (self::$_module !== null) {
				$log_line .= '['.self::$_module.'] ';
			}
			$log_line .= "\t: ".$line;

			fwrite(self::$_stdOut, $log_line);
			echo $log_line;
		}

		return $line;
	}

	/**
	 * Log warning message
	 *
	 * @param String $message warning
	 *
	 * @return String logged message
	 */
	public static function warning($message) {
		if (!is_resource(self::$_stdOut)) {
			self::_checkFileDescriptors();
		}

		return self::_writeMessage($message, self::LVL_WARNING);
	}

	/**
	 * Log exception message
	 *
	 * @param String $message exception
	 *
	 * @return String logged message
	 */
	public static function exception($message) {
		if (!is_resource(self::$_stdOut)) {
			self::_checkFileDescriptors();
		}

		return self::_writeMessage($message, self::LVL_EXCEPTION);
	}


	/**
	 * Log message message
	 *
	 * @param String $message message
	 *
	 * @return String logged message
	 */
	public static function message($message) {
		if (!is_resource(self::$_stdOut)) {
			self::_checkFileDescriptors();
		}

		return self::_writeMessage($message, self::LVL_MESSAGE);
	}


	/**
	 * Log debug message
	 *
	 * @param String $message debug
	 *
	 * @return String logged message
	 */
	public static function debug($message) {
		if (!is_resource(self::$_stdOut)) {
			self::_checkFileDescriptors();
		}

		return self::_writeMessage($message, self::LVL_DEBUG);
	}


	/**
	 * Determine if log level should be logged based on self::log_level.
	 *
	 * @param String $type log type
	 *
	 * @return Bolean true if log type should be logged based on log level
	 */
	private static function _shouldBeLogged($type) {
		$log_levels = array();

		// determine witch level should be displayed
		switch (self::$_log_level) {
		case self::LVL_DEBUG :
			$log_levels[] = self::LVL_DEBUG;
			// fall through, all level below should also be shown
		case self::LVL_MESSAGE :
			$log_levels[] = self::LVL_MESSAGE;
			// fall through, all level below should also be shown
		case self::LVL_WARNING :
			$log_levels[] = self::LVL_WARNING;
			// fall through, all level below should also be shown
		case self::LVL_EXCEPTION :
			$log_levels[] = self::LVL_EXCEPTION;
		}

		// check if given level should be shown
		if (in_array($type, $log_levels)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Log start message
	 *
	 * @param String $message start message
	 *
	 * @return void
	 */
	public static function startLog($message = null) {
		if ($message === null) {
			$message = 'START of log on: '. date('r');
		}

		$string_lenght = strlen($message)+4;

		self::message(str_repeat('*', $string_lenght));
		self::message('* '. $message . ' *');
		self::message(str_repeat('*', $string_lenght));
	}

	/**
	 * Log end message
	 *
	 * @param String $message end message
	 *
	 * @return void
	 */
	public static function endLog($message = null) {
		if ($message === null) {
			$message = 'END of log on: '. date('r');
		}

		$string_lengt = strlen($message)+4;

		self::message(str_repeat('*', $string_lengt));
		self::message('* '. $message . ' *');
		self::message(str_repeat('*', $string_lengt));
	}

	/**
	 * Determine output stream
	 *
	 * @return void
	 */
	private static function _checkFileDescriptors() {
		if (!empty(self::$_log_file_path)) {
			if (!is_resource(self::$_stdOut)) {
				self::$_stdOut = fopen(self::$_log_file_path, 'ab');
			}
		} else {
			if (!is_resource(self::$_stdOut)) {
				self::$_stdOut = fopen('/dev/null', 'ab');
			}
		}
	}

}

?>