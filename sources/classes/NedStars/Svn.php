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
	 * @param String $branch           name of the branch or tag to export
	 * @param String $destination_path absolute path to destination folder
	 * @param String $subfolder        subfolder in git project to get
	 *
	 * @return void
	 */
	public static function getArchive($repository, $branch, $destination_path, $subfolder = null) {
		// make sure input is not empty
		if (empty($repository)) {
			throw new NedStars_SvnException('$repository can not be empty', NedStars_SvnException::EMPTY_REPOSITORY);
		}

		// make sure input is not empty
		if (empty($branch)) {
			throw new NedStars_SvnException('$branch can not be empty', NedStars_SvnException::EMPTY_BRANCH);
		}

		// make sure dir is writable
		if (!is_dir($destination_path)) {
			throw new NedStars_SvnException('$destination_path is not a valid path: '. escapeshellarg($destination_path), NedStars_SvnException::INVALID_PATH);
		}

		// build command, folder in git repo is optional
		$command = 'svn archive --remote '.escapeshellarg($repository).' '.escapeshellarg($branch);
		$result_path= NedStars_FileSystem::getNiceDir($destination_path);
		if ($subfolder !== null) {
			$command .= ' '.escapeshellarg($subfolder);
			$result_path = NedStars_FileSystem::getNiceDir($result_path.$subfolder);
		}
		// extract git tar file into destination folder
		$command .= ' | tar -x -C '.escapeshellarg($destination_path);

		NedStars_Log::debug($command);
		NedStars_Execution::run($command);

		if (!is_dir($result_path) ) {
			throw new NedStars_SvnException('Archive could not be created for: '.$result_path, NedStars_SvnException::ARCHIVE_FAIL);
		}
	}

	/**
	 * Verify credentials and branch by git ls-remote
	 *
	 * @param String $repository ssh repository string
	 * @param String $branch     name of the branch or tag
	 *
	 * @return Boolean true is credentials are ok
	 */
	public static function verifyCredentials($repository, $branch) {
		// make sure input is not empty
		if (empty($repository)) {
			throw new NedStars_SvnException('$repository can not be empty', NedStars_GitException::EMPTY_REPOSITORY);
		}

		// make sure input is not empty
		if (empty($branch)) {
			throw new NedStars_SvnException('$branch can not be empty', NedStars_GitException::EMPTY_BRANCH);
		}

		// TODO check credentials
	}
}
?>