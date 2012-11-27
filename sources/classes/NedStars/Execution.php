<?php
/**
 * Execution functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Execution functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Execution {

	/**
	 * Run a shell exec command with debugging
	 * 
	 * @param String  $cmd          The command
	 * @param Boolean $getExitValue If true the shell_exec result will be given.
	 *
	 * @return Mixed String or Boolean
	 */
	public static function run($cmd, $getExitValue = false) {
		NedStars_Log::debug($cmd);
		$result = shell_exec($cmd);
		
		if ($getExitValue) {
			$result = trim($result);
			return $result;
		} else {
			if ($result === null) {
				return false;
			} else {
				return true;
			}
		}		
	}
}
?>