<?php
/**
 * FileSystem functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * FileSystem functions
 *
 * @project   NedStars PHPlib
 * @category  Nedstars_Tools
 * @package   Nedstars_PHPlib
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class NedStars_FileSystem {

	/**
	 * Copy file fronm location x to y for backup
	 *
	 * @param String $old_file_path absoulte path to origin file
	 * @param String $new_file_path absolute path to destination file
	 *
	 * @return void
	 */
	static public function copyFile($old_file_path, $new_file_path) {
		if (file_exists($old_file_path)) {
			if (!copy($old_file_path, $new_file_path)) {
				throw new NedStars_FileSystemException('Failed to copy file: '. escapeshellarg($old_file_path), NedStars_FileSystemException::COPY_FAILED);
			}
		} else {
			throw new NedStars_FileSystemException('File not found: '. escapeshellarg($old_file_path), NedStars_FileSystemException::FILE_NOT_FOUND);
		}

		return true;
	}

	/**
	 * Copy dir fronm location x to y
	 *
	 * @param String $old_path absoulte path to origin dir
	 * @param String $new_path absoulte path to destination dir
	 *
	 * @return void
	 */
	static public function copyDir($old_path, $new_path) {
		// make sure path exists with line ending
		$old_path = self::_getValidatedDir($old_path);
		$new_path = self::_getValidatedDir($new_path);

		return NedStars_Execution::run('cp -pruf '.escapeshellarg($old_path).' '.escapeshellarg($new_path), true);
	}

	/**
	 * Find files by regext in folder x and move them with same file name to folder y
	 * Function is not recursive
	 *
	 * @param String $regex    reg ex patern, e.g. '/^google(.*).htm/i'
	 * @param String $old_path absoulte path to origin dir
	 * @param String $new_path absoulte path to destination dir
	 *
	 * @return Array list of change files
	 */
	static public function copyFilesByRegEx($regex, $old_path, $new_path) {
		// make sure path exists with line ending
		$old_path = self::_getValidatedDir($old_path);
		$new_path = self::_getValidatedDir($new_path);

		// moved files container
		$moved_files = array();

		// find all html files prefixed with google and copy them for backup
		$handle = opendir($old_path);
		if ($handle) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && is_file($old_path.DIRECTORY_SEPARATOR.$entry)) {
					if (preg_match($regex, $entry)) {
						self::copyFile($old_path.DIRECTORY_SEPARATOR.$entry, $new_path.DIRECTORY_SEPARATOR.$entry);
						$moved_files[] = $entry;
					}
				}
			}
			closedir($handle);
		}

		return $moved_files;
	}

	/**
	 * Check if a binary can be found on the file system.
	 *
	 * @param String $binary_name name of the binary, e.g. 'mysqldump'
	 *
	 * @return Boolean false if binairy could not be found
	 */
	static public function hasBinary($binary_name) {
		if (empty($binary_name)) {
			throw new NedStars_FileSystemException('$binary_name can not be empty', NedStars_FileSystemException::BIN_INVALID);
		}

		if (!NedStars_Execution::run('type -p '.escapeshellarg($binary_name), true)) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if a binaries can be found on the file system.
	 * Alias for hasBinary
	 *
	 * @param Array $binary_names set of binary names, e.g. array('mysqldump', 'git')
	 *
	 * @return Boolean false if one of the binairyies could not be found.
	 */
	static public function hasBinaries($binary_names) {
		// make sure input is an array
		if (!is_array($binary_names)) {
			throw new NedStars_FileSystemException('$binary_names is not a valid array', NedStars_FileSystemException::BIN_INVALID);
		}

		// return true if set is empty
		$not_found_binaries = array();

		// for each binary call hasBinary
		foreach ($binary_names as $binary_name) {
			if (!self::hasBinary($binary_name)) {
				$not_found_binaries[] = $binary_name;
			}
		}

		// return true if set_of_not_found_binaries is empty
		if (count($binary_name) == 0) {
			return true;
		} else {
			return $not_found_binaries;
		}
	}

	/**
	 * Chown a directory and its sub directory
	 *
	 * @param String  $path      Absoulte path of folder to chown
	 * @param String  $user      New user
	 * @param String  $group     New group
	 * @param Boolean $recursive True for sub folders (default = true)
	 *
	 * @return Boolean
	 */
	static public function chownDir($path, $user, $group, $recursive = true) {
		// make sure path exists with line ending
		$path = self::_getValidatedDir($path);

		if (!NedStars_Execution::run('chown '.($recursive?'-R ':' ').escapeshellarg($user).':'.escapeshellarg($group).' '.escapeshellarg($path), true)) {
			return false;
		}

		return true;

	}

	/**
	 * Chmod  a directory and its sub directory
	 *
	 * @param String  $path      Absoulte path of folder to chmod
	 * @param String  $mode      New right level
	 * @param Boolean $recursive True for sub folders (default = true)
	 *
	 * @return Boolean
	 */
	static public function chmodDir($path, $mode, $recursive = true) {
		// make sure path exists with line ending
		$path = self::_getValidatedDir($path);

		if (!NedStars_Execution::run('chmod '.($recursive?'-R ':' ').escapeshellarg($mode).' '.escapeshellarg($path), true)) {
			return false;
		}

		return true;
	}

	/**
	 * Backup a folder by making a Tar file of it.
	 *
	 * @param String $path           absoulte path of folder to archive
	 * @param String $dest_file_path absoulte path to destination file, e.g. /var/backup/backup201210261760.tar
	 *
	 * @return void
	 */
	static public function backupDir($path, $dest_file_path) {
		// make sure path exists with a line ending
		$path = self::_getValidatedDir($path);

		$return = NedStars_Execution::run('tar -czPf '.escapeshellarg($dest_file_path).' '.escapeshellarg($path), true);

		// make sure the backup file is created
		if (!file_exists($dest_file_path) ) {
			throw new NedStars_FileSystemException('Backup file not created: '.$dest_file_path, NedStars_FileSystemException::FILE_NOT_FOUND);
		}

		return $return;
	}

	/**
	 * Switch dir x into y if neeed backup into z
	 *
	 * @param String $folder_path   absoulte path of current data folder, this data will be swaped out for new data
	 * @param String $new_data_path absoulte path of new data location
	 * @param String $backup_path   if absoulte path provided the current data will be moved to here.
	 *
	 * @return Boolean if success
	 */
	static public function relocateDir($folder_path, $new_data_path, $backup_path  = false) {
		// make sure path exists with line ending
		// valid backup path check is done in self::deleteDir() see if statement below
		$folder_path = self::_getValidatedDir($folder_path);
		$new_data_path = self::_getValidatedDir($new_data_path);

		// move folder and if needed or clear out data
		if ($backup_path) {
			// remove content on backup location
			self::deleteDir($backup_path);
			// move current location to backup location
			NedStars_Execution::run('mv '.escapeshellarg($folder_path).' '.escapeshellarg($backup_path));
		} else {
			// remove current data so dat new data can be inserted.
			self::deleteDir($folder_path);
		}

		// move new data into folder
		return NedStars_Execution::run('mv '.escapeshellarg($new_data_path).' '.escapeshellarg($folder_path));
	}

	/**
	 * Delete given dir recursive
	 *
	 * @param String $path absoulte path of folder to delete
	 *
	 * @return Boolean
	 */
	static public function deleteDir($path) {
		// make sure path exists with line ending
		$path = self::_getValidatedDir($path);

		return NedStars_Execution::run('rm -rf '.escapeshellarg($path), true);
	}
	/**
	 * Delete content of a given dir recursive
	 *
	 * @param String $path absoulte path of folder to delete
	 *
	 * @return Boolean
	 */
	static public function deleteDirContent($path) {
		// make sure path exists with line ending
		$path = self::_getValidatedDir($path);

		return NedStars_Execution::run('rm -rf '.escapeshellarg($path.DIRECTORY_SEPARATOR), true);
	}

	/**
	 * Return unxi file permissions
	 *
	 * @param String $file_path absoulte filen path to get permission from
	 *
	 * @return string
	 */
	public static  function getFilePermisions($file_path) {
		// make sure dir is writable
		if (!is_file($file_path)) {
			throw new NedStars_FileSystemException('$file_path is not a valid file: '.escapeshellarg($file_path), NedStars_FileSystemException::FILE_NOT_FOUND);
		}
		return substr(sprintf('%o', fileperms($file_path)), -4);
	}

	/**
	 * Delete files older than N day's
	 *
	 * @param String  $path         absoulte path of folder from wich files should be cleared.
	 * @param integer $days_to_keep number of day's that a file should be kept
	 *
	 * @return Type Description
	 */
	public static function deleteOldFiles($path, $days_to_keep) {
		// make sure path exists with line ending
		$path = self::_getValidatedDir($path);

		$handle = opendir($path);
		if ($handle) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != ".." && is_file($path.$entry)) {
					if (filemtime($path.$entry)  <= (time()-(60*60*24*$days_to_keep))) {
						unlink($path.$entry);
					}
				}
			}
			closedir($handle);
		}
	}

	/**
	 * Make path correct for line ending and check is dir exists
	 *
	 * @param String $path Absolute path
	 *
	 * @return String Absolute path with trailing DIRECTORY_SEPARATOR
	 * @throws NedStars_FileSystemException When directory is not valid.
	 */
	private static function _getValidatedDir($path) {
		$path = self::getNiceDir($path);

		// make sure dir is writable
		if (!is_dir($path)) {
			throw new NedStars_FileSystemException('Folder not found: '.escapeshellarg($path), NedStars_FileSystemException::DIR_NOT_FOUND);
		}

		return $path;
	}

	/**
	 * Get path with line ending DIRECTORY_SEPARATOR
	 *
	 * @param String $path Absolute path
	 *
	 * @return String Absolute path with trailing DIRECTORY_SEPARATOR
	 */
	public static function getNiceDir($path) {
		$path = trim($path);
		if ( DIRECTORY_SEPARATOR != substr($path, -1, 1)) {
			$path .= DIRECTORY_SEPARATOR;
		}

		return $path;
	}

	/**
	 * Get Disk size of a directory
	 *
	 * @param String $path Absolute path
	 *
	 * @return Int Size of folder on disk in bytes
	 */
	public static function getDirectorySize($path) {
		$totalsize = 0;
		if ($handle = opendir($path)) {
			while (false !== ($file = readdir($handle))) {
				$nextpath = $path . '/' . $file;
				if ($file != '.' && $file != '..' && !is_link($nextpath)) {
					if (is_dir($nextpath)) {
						$totalsize += NedStars_FileSystem::getDirectorySize($nextpath);
					} elseif (is_file($nextpath)) {
						$totalsize += filesize($nextpath);
					}
				}
			}
		}
		closedir($handle);

		return $totalsize;
	}

	/**
	 * Get humal readably file size back e.g. 1.9 MiB
	 *
	 * @param int $bytes size  in bytes to reformat.
	 *
	 * @return String formated file / folder size
	 */
	public static function getSymbolByQuantity($bytes) {
		$symbols = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
		$exp = floor(log($bytes)/log(1024));

		return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));
	}

	/**
	 * Pops off the last folder from a given path
	 *
	 * @param String $path      Absolute or releative path
	 * @param String $separator Directory seperator to look for
	 *
	 * @return String reformated path
	 */
	public static function popDir($path, $separator = DIRECTORY_SEPARATOR) {
		// stripoff Directory seperator if attached to the end
		if ( DIRECTORY_SEPARATOR == substr($path, -1, 1)) {
			$path = substr($path, 0, -1);
		}
		// pop off last folder
		$parts = explode($separator, $path);
		array_pop($parts);
		// reformat
		return implode($separator, $parts);
	}

	/**
	 * Create a dir if not existing
	 *
	 * @param String $path Absolute or releative path
	 *
	 * @return void
	 * @throws NedStars_FileSystemException When directory could not be created.
	 */
	public static function createDirIfNeeded($path) {
		if (!is_dir($path)) {
			if (mkdir($path)) {
				NedStars_Log::message('Directory created:'. escapeshellarg($path));
			} else {
				throw new NedStars_FileSystemException('Could not create dir: '.$path, NedStars_FileSystemException::DIR_NOT_FOUND);
			}

		}
	}
}
?>