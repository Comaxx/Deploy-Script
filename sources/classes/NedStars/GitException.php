<?php
/**
 * Git Exception class
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Git Exception class, Logs msg via NedStars_Log
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_GitException extends NedStars_Exception {

	const EMPTY_BRANCH 		= 1;
	const EMPTY_REPOSITORY	= 2;
	const INVALID_PATH 		= 3;
	const EMPTY_REVISION	= 4;
	const ARCHIVE_FAIL		= 5;
	const CLONE_FAIL		= 6;

}