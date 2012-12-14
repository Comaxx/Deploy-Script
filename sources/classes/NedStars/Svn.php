<?php

/**
 * SVN functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Svn {

	/**
	 * Get svn export into folder
	 *
	 * @param String $repository       ssh repository string
	 * @param String $username         svn username
	 * @param String $password         svn password
	 * @param String $destination_path absolute path to destination folder
	 *
	 * @return void
	 */
	public static function getArchive($repository, $username, $password, $destination_path) {
		// make sure input is not empty
		if (empty($repository)) {
			throw new NedStars_SvnException('$repository can not be empty', NedStars_SvnException::EMPTY_REPOSITORY);
		}

		// make sure dir is writable
		if (!is_dir($destination_path)) {
			throw new NedStars_SvnException('$destination_path is not a valid path: '. escapeshellarg($destination_path), NedStars_SvnException::INVALID_PATH);
		}

		// build command, folder in svn repo is optional
		$command = 'svn export --force --no-auth-cache --username '.escapeshellarg($username);
		if (!empty($password) && $password !== false) {
			$command .= ' --password '.escapeshellarg($password);
		}
		$command .= ' '.escapeshellarg($repository);

		// add destionation path
		$result_path= NedStars_FileSystem::getNiceDir($destination_path);
		$command .= ' '.escapeshellarg($destination_path);

		$result = NedStars_Execution::run($command, true);

		// make sure path and result are there (svn give empty result on error)
		if (!is_dir($result_path) || empty($result)) {
			throw new NedStars_SvnException('Archive could not be created for: '.$result_path, NedStars_SvnException::ARCHIVE_FAIL);
		}
	}

	/**
	 * Verify credentials and branch by svn ...
	 *
	 * @param String $repository ssh repository string
	 * @param String $username   svn username
	 * @param String $password   svn password
	 *
	 * @return Boolean true is credentials are ok
	 */
	public static function verifyCredentials($repository, $username, $password) {
		// make sure input is not empty
		if (empty($repository)) {
			throw new NedStars_SvnException('$repository can not be empty', NedStars_SvnException::EMPTY_REPOSITORY);
		}

		// make sure input is not empty
		if (empty($username)) {
			throw new NedStars_SvnException('$username can not be empty', NedStars_SvnException::EMPTY_PROPERTY);
		}

		// make sure input is not empty
		if (empty($password)) {
			throw new NedStars_SvnException('$password can not be empty', NedStars_SvnException::EMPTY_PROPERTY);
		}

		// build command, folder in svn repo is optional
		$command = 'svn info --no-auth-cache --username '.escapeshellarg($username);
		if (!$password !== false) {
			$command .= ' --password '.escapeshellarg($password);
		}
		$command .= ' '.escapeshellarg($repository);

		$result = NedStars_Execution::run($command, true);
		if (empty($result)) {
			return false;
		}
		return true;
	}
}
?>