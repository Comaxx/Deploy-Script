#!/usr/bin/php
<?php
/**
 * Deploy a git source to environment
 *
 * @project   NedStars Deployer
 * @category  Instance
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

require_once 'includes/bootstrap.php';

// run script
try {
	//Parse arguments
	$options = NedStars_Arguments::parse(Deployer::$input_option_array);
	// check if we have atleast one argument, else print usage.
	if (count($options) == 0) {
		Deployer::printUsage();
		exit(1);
	}
	// Output version if option version was given.
	if (isset($options['version'])) {
		Deployer::printVersion();
		exit(0);
	}

	$deployer = new Deployer($options);

	// TODO: Maintanance page

	// Get git source
	$deployer->getSource();

	// Backup mysql data
	$deployer->backupMysql();

	// Backup live data to tar
	$deployer->backupLive();

	// Move the non versioned files to source dir.
	$deployer->preserveData();

	// Clear out tmp data like session and cache
	$deployer->clearData();

	// Apply correct permissions
	$deployer->setFolderPermisions();

	// Switch folders so that everything is live
	$deployer->switchLive();

	// Inform subscribers
	$deployer->sendNotifications();

	// Remove old files
	$deployer->purgeOldBackups();


} catch (DeployerException $exception) {
	// set exit code
	$exit_code = 100;
	$exception_message = $exception->getMessage();
} catch (NedStars_GitException $exception) {
	// set exit code
	$exit_code = 101;
	$exception_message = $exception->getMessage();
} catch (NedStars_ArgumentsException $exception) {
	// set exit code
	$exit_code = 102;
	$exception_message = $exception->getMessage();
} catch (NedStars_FileSystemException $exception) {
	// set exit code
	$exit_code = 103;
	$exception_message = $exception->getMessage();
} catch (NedStars_LogException $exception) {
	// set exit code
	$exit_code = 104;
	$exception_message = strval($exception);

} catch (Exception $exception) {
	// set exit code
	$exit_code = 99;
	$exception_message = strval($exception);
}
if (isset($exit_code)) {
	// Show Exception and exit with error code (nice red terminal color)
	printf("\033[31;31m[%s] %s\033[0m".PHP_EOL, $exit_code.'-'.$exception ->getCode(), $exception_message);
	exit($exit_code);
}

// Return the exit value to the OS.(ok)
printf("\033[0;32m%s\033[0m".PHP_EOL, ' > > > OINK it\'s Done');
exit(0);
?>