<?php

/**
 * SVN Exception class, Logs msg via NedStars_Log
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_SvnException extends NedStars_Exception {

	const EMPTY_REPOSITORY	= 1;
	const INVALID_PATH 		= 2;
	const ARCHIVE_FAIL		= 3;
	const EMPTY_PROPERTY	= 4;

}