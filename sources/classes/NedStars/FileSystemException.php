<?php
/**
 * FileSystem Exception class
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * FileSystem Exception class, Logs msg via NedStars_Log
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_FileSystemException extends NedStars_Exception {

	const COPY_FAILED 		 = 1;
	const FILE_NOT_FOUND 	 = 2;
	const DIR_NOT_FOUND		 = 3;
	const BIN_INVALID		 = 4;
    const ACTION_NOT_ALLOWED = 5;
}