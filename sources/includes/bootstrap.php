<?php
/**
 * Bootstrap code to take care of the initial application initialization
 *
 * @project   NedStars Deployer
 * @category  Bootstrap
 * @package   Nedstars_Deployer
 * @author    Bas Peters, Nedstars <bas.peters@nedstars.nl>
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

define('ROOT', realpath(dirname(__FILE__) . '/..') . '/');
define('CLASSES', ROOT . 'classes' . '/');

ini_set('display_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Amsterdam');

// change working dit to project path, that's the parent folder for source folder.
chdir(dirname($_SERVER['argv'][0]));
chdir("../");

/**
 * Autoload handler
 *
 * @param String $className The class name to autoload
 *
 * @return void
 */
function autoloadHandler($className) {
	if (file_exists(CLASSES . str_replace('_', '/', $className) . '.php')) {
		include_once CLASSES . str_replace('_', '/', $className) . '.php';
	}
}

/**
 * Error Handler
 *
 * @param Int    $errorNumber Error number
 * @param String $errorString Error description
 * @param String $errorFile   Filename where the error occurred
 * @param Int    $errorLine   Line number where the error occurred
 *
 * @return Bool true when the error is considered handled and not cascaded
 */
function errorHandler($errorNumber, $errorString, $errorFile, $errorLine) {
    if (!(error_reporting() & $errorNumber)) {
        return;
    }
    throw new ErrorException($errorString.' ('.basename($errorFile).":{$errorLine})", 0, $errorNumber, $errorFile, $errorLine);
}

// register handlers
spl_autoload_register('autoloadHandler');
set_error_handler('errorHandler');
