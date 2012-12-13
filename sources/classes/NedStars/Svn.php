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
		if (!empty($password)) {
			$command .= ' --password '.escapeshellarg($password);
		}
		$command .= ' '.escapeshellarg($repository);

		// add destionation path
		$result_path= NedStars_FileSystem::getNiceDir($destination_path);
		$command .= ' '.escapeshellarg($destination_path);

		NedStars_Execution::run($command, true);

		if (!is_dir($result_path) ) {
			throw new NedStars_SvnException('Archive could not be created for: '.$result_path, NedStars_SvnException::ARCHIVE_FAIL);
		}
	}

	/**
	 * Verify credentials and branch by svn ...
	 *
	 * @param String $repository ssh repository string
	 *
	 * @return Boolean true is credentials are ok
	 */
	public static function verifyCredentials($repository) {
		// make sure input is not empty
		if (empty($repository)) {
			throw new NedStars_SvnException('$repository can not be empty', NedStars_SvnException::EMPTY_REPOSITORY);
		}

		// TODO: check credentials
	}
}
?>