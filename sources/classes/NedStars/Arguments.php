<?php
/**
 * Argument Parser
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Argument Parser
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Arguments {

	/**
	 * Parse arguments via PHP getOpt() function but picks the one that is given for short or long variable.
	 * if short argument is given als boolean, it will output true.
	 *
	 * array(
	 * 	'debug' => array(
	 * 		'short' => 'd',
	 * 		'long'	=> 'debug',
	 * 		'type'	=> '::',
	 * 	)
	 * )
	 *
	 * @param array $option_array key is return key, value is array with optional keys -> short, long, type
	 *
	 * @return array
	 */
	public static function parse($option_array) {
		// build options for getopt function
		$shortopts = self::_buildShortOptions($option_array);
		$longopts  = self::_buildLongOptions($option_array);

		// log info
		NedStars_Log::setModule(__CLASS__);
		NedStars_Log::debug('shortopts: "'.$shortopts.'"');
		NedStars_Log::debug('longopts: "'.implode(', ', $longopts).'"');

		// let PHP parse the data
		$options = getopt($shortopts, $longopts);

		// data that will be returned
		$return_options = array();

		// loop through php found options and match them to configured options
		foreach ($option_array as $option => $option_info) {
			// check if we already have a value, prevent short and long info
			$found_option = false;

			// set short value
			if (isset($option_info['short']) && isset($options[$option_info['short']])) {
				$return_options[$option] = $options[$option_info['short']];
				// throw exception if long version is found, later on
				$found_option = true;
			}

			// set long value
			if (isset($option_info['long']) && isset($options[$option_info['long']])) {
				$return_options[$option] = $options[$option_info['long']];

				// it is not posible to have both short and long
				if ($found_option) {
					throw new NedStars_ArgumentsException('Found short and long argument: -'.$option_info['short'].' --'.$option_info['long'], NedStars_ArgumentsException::DOUBLE_ARGUMENTS);
				}
			}

			// convert value
			if (isset($return_options[$option])) {
				$return_options[$option] = self::_convertBooleans($return_options[$option]);
				// log found option
				NedStars_Log::debug('Option found '.$option.' "'.$return_options[$option].'"');
			}

		}

		// unset log module
		NedStars_Log::setModule(null);

		return $return_options;
	}

	/**
	 * Convert PHP boolean input to real booleans
	 * If no boolean found return value
	 *
	 * @param mixed $value value that may be a boolean
	 *
	 * @return mixed value
	 */
	private static function _convertBooleans($value) {
		if ($value === false) {
			return true; // convert default to true
		} else if ($value === "false") {
			return false; // if user input explicitly = string false
		} else if ($value === "true") {
			return true; // if user input explicitly = string true
		} else {
			return $value; // if it is no boolean
		}
	}

	/**
	 * Build the string that getOpt() needs for SHORT based on the argumt array
	 *
	 * @param array $option_array a valid argument parser array
	 *
	 * @return String valid getOpt() SHORT input string
	 */
	private static function _buildShortOptions($option_array) {
		// getOpt() SHORT needs a String
		return implode('', self::_buildOptions($option_array, 'short'));
	}

	/**
	 * Build the array that getOpt() needs for LONG based on the argumt array
	 *
	 * @param array $option_array a valid argument parser array
	 *
	 * @return array valid getOpt() LONG input array
	 */
	private static function _buildLongOptions($option_array) {
		// getOpt() LONG needs a array
		return self::_buildOptions($option_array, 'long');
	}

	/**
	 * Build the array that getOpt() needs for $type based on the argumt array
	 *
	 * @param array  $option_array a valid argument parser array
	 * @param String $type         short or long depending on the type
	 *
	 * @return array valid getOpt()
	 */
	private static function _buildOptions($option_array, $type) {
		// prepare return array
		$return_options = array();

		// for each option, if isset type key add with type
		foreach ($option_array as $option) {
			if (isset($option[$type]) && !empty($option[$type])) {
				$return_option = $option[$type];

				if (isset($option['type']) && !empty($option['type'])) {
					$return_option .= $option['type'];
				}

				// add to return array
				$return_options[] = $return_option;
			}
		}

		return $return_options;
	}
}
