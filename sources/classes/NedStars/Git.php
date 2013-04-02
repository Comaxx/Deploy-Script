<?php
/**
 * Git functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Git functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_Git {
	/**
	 * Git lab send a string of zero's if there is no SHA1 for the after or before branch
	 */
	const EMPTY_SHA1 = '0000000000000000000000000000000000000000';

	/**
	 * Get git export into folder
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
			throw new NedStars_GitException('$repository can not be empty', NedStars_GitException::EMPTY_REPOSITORY);
		}

		// make sure input is not empty
		if (empty($branch)) {
			throw new NedStars_GitException('$branch can not be empty', NedStars_GitException::EMPTY_BRANCH);
		}

		// make sure dir is writable
		if (!is_dir($destination_path)) {
			throw new NedStars_GitException('$destination_path is not a valid path: '. escapeshellarg($destination_path), NedStars_GitException::INVALID_PATH);
		}

		// build command, folder in git repo is optional
		$command = 'git archive --remote '.escapeshellarg($repository).' '.escapeshellarg($branch);
		$result_path= NedStars_FileSystem::getNiceDir($destination_path);
		if ($subfolder !== null) {
			$command .= ' '.escapeshellarg($subfolder);
			$result_path = NedStars_FileSystem::getNiceDir($result_path.$subfolder);
		}
		// extract git tar file into destination folder
		$command .= ' | tar -x -C '.escapeshellarg($destination_path);

		NedStars_Execution::run($command);

		if (!is_dir($result_path) ) {
			throw new NedStars_GitException('Archive could not be created for: '.$result_path, NedStars_GitException::ARCHIVE_FAIL);
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
			throw new NedStars_GitException('$repository can not be empty', NedStars_GitException::EMPTY_REPOSITORY);
		}

		// make sure input is not empty
		if (empty($branch)) {
			throw new NedStars_GitException('$branch can not be empty', NedStars_GitException::EMPTY_BRANCH);
		}

		// check if remote branch is ok.
		$command= 'git ls-remote '.escapeshellarg($repository).' '.escapeshellarg($branch);
		if (trim(NedStars_Execution::run($command, true)) != '') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Update to latest version for a existing project
	 *
	 * @param String $project_path Absolute path to project dir
	 * @param String $revision     The revision to go to
	 *
	 * @return Mixed exec result
	 */
	public static function doCheckout($project_path, $revision) {
		// make sure input is not empty
		if (empty($revision)) {
			throw new NedStars_GitException('$revision can not be empty', NedStars_GitException::EMPTY_REVISION);
		}

		// make sure dir is writable
		$project_path = NedStars_FileSystem::getNiceDir($project_path);
		if (!is_dir($project_path)) {
			throw new NedStars_GitException('$project_path is not a valid path: '. escapeshellarg($project_path), NedStars_GitException::INVALID_PATH);
		}

		$command = "cd ".escapeshellarg($project_path)."; git checkout ".escapeshellarg($revision);
		return NedStars_Execution::run($command, true);

	}

	/**
	 * Get latest meta info	for existing project
	 *
	 * @param String $project_path Absolute path to project dir
	 *
	 * @return Mixed exec result
	 */
	public static function doFetch($project_path) {
		// make sure dir is writable
		$project_path = NedStars_FileSystem::getNiceDir($project_path);
		if (!is_dir($project_path)) {
			throw new NedStars_GitException('$project_path is not a valid path: '. escapeshellarg($project_path), NedStars_GitException::INVALID_PATH);
		}

		$command = "cd ".escapeshellarg($project_path)."; git fetch";
		return NedStars_Execution::run($command, true);

	}

	/**
	 * Make a Git clone in base project dir
	 *
	 * @param String $base_dir     path to base folder, a sub folder will be create in this dir.
	 * @param String $server       server name without git@, e.g. example.com
	 * @param String $project_name repro name on server
	 *
	 * @return Boolean true
	 */
	public static function makeClone($base_dir, $server, $project_name) {
		// make sure dir is writable
		$base_dir = NedStars_FileSystem::getNiceDir($base_dir);
		if (!is_dir($base_dir)) {
			throw new NedStars_GitException('$base_dir is not a valid path: '. escapeshellarg($base_dir), NedStars_GitException::INVALID_PATH);
		}

		// check if new sub folder does not exists
		if (is_dir(NedStars_FileSystem::getNiceDir($base_dir.$project_name))) {
			throw new NedStars_GitException('New folder already exists: '. escapeshellarg($base_dir.$project_name), NedStars_GitException::INVALID_PATH);
		}

		$command = 'cd '.escapeshellarg($base_dir).'; git clone '.escapeshellarg('git@'.$server.':'.$project_name.'.git');

		NedStars_Execution::run($command, true);

		if (is_dir($base_dir.$project_name)) {
			NedStars_Log::message('Git clone success');
			return true;
		} else {
			throw new NedStars_GitException('Git Clone failed: '.$project_name, NedStars_GitException::CLONE_FAIL);
		}
	}

	/**
	 * Get the revision where the current branch is branched off from.
	 *
	 * @param String $project_path Absolute path to project
	 *
	 * @return String short sha1 hash.
	 */
	public static function getPreviousRevision($project_path) {
		$command = "cd ".escapeshellarg($project_path)."; git log --oneline -50";
		$output = explode(PHP_EOL, NedStars_Execution::run($command, true));

		$previous_ref = null;
		foreach ($output as $line) {
			$parts = explode(' ', $line, 2);

			$hash = $parts[0];
			$command = "cd ".escapeshellarg($project_path)."; git name-rev ".$parts[0]; // output: hash ref~history
			$raw_branch_info = NedStars_Execution::run($command, true);
			$branch_info = trim(str_replace($parts[0], '', $raw_branch_info));

			$parts = explode('~', $branch_info, 2);
			$reference = $parts[0];

			if ($previous_ref == null) {
				// init ref
				$previous_ref = $reference;
			} elseif ($previous_ref != $reference) {
				// found a ref that's not in the topic / branch
				return $hash;
			}
		}
	}

}
