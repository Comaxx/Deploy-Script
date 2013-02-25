<?php
/**
 * Deployer Exception class
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Deployer Exception class, Logs msg via NedStars_Log
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class DeployerException extends NedStars_Exception {

	const DISK_SPACE 			= 1;
	const NO_USER_RIGHTS		= 2;
	const NO_LOCK				= 3;
	const BINARY_MISSING		= 4;
	const MYSQL_FAIL			= 5;
	const DIR_FAIL				= 6;
	const ARCHIVE_CREDENTIALS	= 7;
	const CONFIG_FAIL			= 8;
	const LOCAL_XML_FAILED		= 9;
	const PRINT_VERSION			= 10;
	const ARCHIVE_TYPE_MISSING	= 11;

}