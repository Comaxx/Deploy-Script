<?php
/**
 * Deployer class, main deploy boject.
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Deployer class, main deploy boject.
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class Deployer {

	/**
	 * Script version
	 */
	const VERSION = '1.3';
	/**
	 * Configuration object
	 *
	 * @var DeployConfig Configuration object
	 */
	private $_config = null;


	private $_time_start = null;

	/**
	 * PHP CLI arguments array.
	 *
	 * @var Array argument array used for NedStars_Arguments
	 */
	public static $input_option_array = array(
			'debug' => array(
				'short' => 'd',
				'long'	=> 'debug',
				'type'	=> '::',
				),
			'quiet' => array(
				'short' => 'q',
				'long'	=> 'quiet',
				'type'	=> '::',
				),
			'config' => array(
				'short' => 'c',
				'long'	=> 'config',
				'type'	=> ':',
				),
			'tag' => array(
				'short' => 't',
				'long'	=> 'tag',
				'type'	=> ':',
				),
			'branch' => array(
				'short' => 'b',
				'long'	=> 'branch',
				'type'	=> ':',
				),
			'version' => array(
				'short'	=> 's',
				'long'	=> 'version',
				'type'	=> '',
				),
		);

	/**
	 * Contains the lock resources.
	 *
	 * @var resource socket stream for locking
	 */
	private $_lock = null;

	/**
	 * Constructor
	 *
	 * 1) Parse arguments (done outside of the object)
	 * 2) set lock to prevent multiple instances
	 * 3) Load config
	 * 4) Init log level and set start msg
	 * 5) Sanity_info: check if all configuration options are filled in
	 *    	Check if user is root
	 *    	Check if mysql and git credentials are present
	 * 6) Check if required binairies exist (config should be checked)
	 * 7) Check for free disk space
	 *
	 * @param Array $options set options
	 *
	 * @return void
	 */
	function __construct($options) {
		$this->_time_start = microtime(true);

		// 2) set lock to prevent multiple instances
		$this->_setLock();


		// 3) Load config file and override with option values if needed
		$this->_loadConfigFile($options);

		// 4) Init log level and set start msg
		$this->_startLog($options);

		// 5) Sanity_info: check if all configuration options are filled in
		$this->_checkConfigurationValues();

		// 6) Check if required binairies exist
		$this->_checkBinaries();

		// 7) Check for free disk space
		$this->_checkFreeDiskSpace();

		// Start is ok
		NedStars_Log::message('Deployer input is correct.');
	}

	/**
	 * Clean up locks
	 *
	 * @return void
	 */
	public function __destruct() {
		// release lock if any
		$this->_unsetLock();
	}

	/**
	 * Print info on how to use the class
	 *
	 * @return void
	 */
	public static function printUsage() {
		echo "\n";
		echo 'Usage: '.$_SERVER['argv'][0].' <subcommands>'."\n";
		echo "\n";
		echo "Available subcommands are:\n";
		echo "   -c <name>			Alias for --config.\n";
		echo "   -t <tag #>			Alias for --tag.\n";
		echo "   -b <branch>			Alias for --branch.\n";
		echo "   -d 				Alias for --debug.\n";
		echo "   -q					quiet for --verbose.\n";
		echo "   --config <name>		will set file deploy.<name>.conf.php.\n";
		echo "   --tag <tag #>		Tag to be deployed.\n";
		echo "   --branch <branch>		Branch to be deployed.\n";
		echo "   --debug 			Debug modes: default = false.\n";
		echo "   --quiet			Quiet modes, only output warning and exceptions, only if debug is not given: default = false.\n";
		echo "   --version    		Shows version information of Deploy.\n";
		echo "\n";
	}

	/**
	 * Print version info to terminal
	 *
	 * @return void
	 */
	public static function printVersion() {
		$message = 'Deploy script, Version: '.self::VERSION;
		printf("\033[0;32m%s\033[0m".PHP_EOL, $message);
	}

	/**
	 * Set lock to prevent multiple instances on the same server
	 *
	 * Uses stream socket server op port 12345 to lock
	 *
	 * @return void
	 * @throws Exception when lock is allready set
	 * @throws Exception when a other proces has the lock
	 */
	private function _setLock() {
		if ($this->_lock !== null) {
			throw new DeployerException('Lock is already set.', DeployerException::NO_LOCK);
		}

		try {
			$this->_lock = stream_socket_server("tcp://0.0.0.0:12345");
		} catch (Exception $e) {
			throw new DeployerException('Could not get lock! Is the proces allready running on this server?', DeployerException::NO_LOCK);
		}
	}

	/**
	 * Relase the locking mechanism if set
	 *
	 * @return void
	 */
	private function _unsetLock() {
		if ($this->_lock !== null) {
			fclose($this->_lock);
			$this->_lock = null;
		}
	}


	/**
	 * Make start log msg and set log level
	 * NedStars_Log::setLogLevel($display_level);
	 * NedStars_Log::startLog($start_msg);
	 *
	 * @param Array $options set of options, tag, branch, quiet
	 *
	 * @return void
	 */
	private function _startLog($options) {
		$start_msg = 'Start Deployment';
		if (isset($options['tag'])) {
			$start_msg .= '  Tag: '.$options['tag'];
		}
		if (isset($options['branch'])) {
			$start_msg .= '  '.$options['branch'];
		}

		if ($this->_config->is_debug_modus) {
			$display_level = NedStars_Log::LVL_DEBUG;
		} elseif (isset($options['quiet']) && $options['quiet']) {
			$display_level = NedStars_Log::LVL_WARNING;
		} else {
			$display_level = NedStars_Log::LVL_MESSAGE;
		}

		NedStars_Log::setLogLevel($display_level);
		NedStars_Log::startLog($start_msg);
	}
	/**
	 * Verify MySQL credentials by making a connection to database
	 *
	 * @return boolean true
	 */
	private function _verifyMysqlCredentials() {
		try {

			foreach ($this->_config->databases as $config_database) {

				if ($config_database->password !== false and empty($config_database->password)) {
					$password = NedStars_Execution::prompt('Enter MySQL password (' . $config_database->username . '@' . $config_database->host . ':' . implode(', ', $config_database->dbnames) . '): ', true);
					if (empty($password)) {
						$password = false;
					}
					$config_database->password = $password;
				}

				$connection = mysql_connect(
					$config_database->host,
					$config_database->username,
					$config_database->password
				);
				// throw exception if database could not be selected
				foreach ($config_database->dbnames as $dbname) {
					if (!mysql_select_db($dbname)) {
						throw new Exception('Database connection failed on ' . $config_database->username . '@' . $config_database->host . ':' . $dbname . '');
					}
				}
				// close connection after test
				mysql_close($connection);
				unset($connection);

			}

		} catch (Exception $exception) {
			// register exception and rethrow.
			throw new DeployerException($exception->getMessage(), DeployerException::MYSQL_FAIL);
		}

		return true;
	}

	/**
	 * Helper function to check archive credentials.
	 *
	 * @return void
	 * @throws NedStars_Git when git credentials fail
	 * @throws Exception if no archive type given.
	 */
	private function _verifyArchiveCredentials() {
		$config_archive = $this->_config->archive;

		switch(strtolower($config_archive->type)) {
		case 'svn' :
			if (empty($config_archive->svn->password)) {
				$password = NedStars_Execution::prompt('Enter SVN password (' . $config_archive->svn->username . '@' . $config_archive->svn->repo . '): ', true);
				if (empty($password)) {
					$password = false;
				}
				$config_archive->svn->password = $password;
			}

			if (!NedStars_Svn::verifyCredentials($config_archive->svn->repo, $config_archive->svn->username, $config_archive->svn->password)) {
				throw new DeployerException('SVN credentials or branch are incorrect', DeployerException::ARCHIVE_CREDENTIALS);
			}

			// TODO: add credential check
			break;
		case 'git' :
			if (!NedStars_Git::verifyCredentials($config_archive->git->repo, $config_archive->git->branch)) {
				throw new DeployerException('Git credentials or branch are incorrect', DeployerException::ARCHIVE_CREDENTIALS);
			}
			break;
		default:
			throw new DeployerException('No archive type found: '.strtolower($config_archive->type), DeployerException::ARCHIVE_TYPE_MISSING);
			break;
		}

	}

	/**
	 * Check if configuration values are present and correct
	 *
	 * @return void
	 */
	private function _checkConfigurationValues() {
		// check if dir's exist. if not create them
		NedStars_FileSystem::createDirIfNeeded($this->_config->paths->web_live_path);
		NedStars_FileSystem::createDirIfNeeded($this->_config->paths->temp_new_path);
		NedStars_FileSystem::createDirIfNeeded($this->_config->paths->temp_old_path);
		if ($this->_config->backup->make_file_backup || $this->_config->backup->make_database_backup) {
			NedStars_FileSystem::createDirIfNeeded($this->_config->backup->folder);
		}

		// check if mysql credentials are ok.
		// setup a db connection to test credentials
		// but only if backup should be made
		if ($this->_config->backup->make_database_backup) {
			$this->_verifyMysqlCredentials();
		}

		// check if user is root
		$this->_verifyRootUser();

		// Check if archive credentials are ok.
		$this->_verifyArchiveCredentials();
	}

	/**
	 * Load specified config file
	 * Default config file is "deploy.conf.php"
	 *
	 * @param array $options set of posible overrides: branch, tag, config, debug
	 *
	 * @return void
	 */
	private function _loadConfigFile($options) {

		if (isset($options['config'])) {
			$config_file = $options['config'].'.conf.xml';
		} else {
			$config_file = 'deploy.conf.xml';
		}

		// Main Configuration object
		$config = new DeployConfig();

		DeployConfig::parseData($config, $config_file);

		// Override config values with given options
		// Branch
		if (isset($options['branch'])) {
			$config->archive->git->branch = 'heads/'.$options['branch'];
		}

		// Tag
		if (isset($options['tag'])) {
			$config->archive->git->branch = 'tags/'.$options['tag'];
		}

		// Debug
		if (isset($options['debug'])) {
			$config->is_debug_modus = $options['debug'];
		}

		$this->_config = $config;
	}

	/**
	 * Logic for preserving  data
	 *
	 * @return void
	 */
	public function preserveData() {
		NedStars_Log::message('Start preserving data.');

		// copy media files from the old live to the new environment
		foreach ($this->_config->preserve_data->folders as $dir_path) {
			$current_path = NedStars_FileSystem::getNiceDir($this->_config->paths->web_live_path.'/'.$dir_path);
			$new_path = NedStars_FileSystem::getNiceDir($this->_getSourceFolder().$dir_path);

			if (is_dir($current_path)) {
				if (!is_dir($new_path)) {
					// try to make dir.
					if (!mkdir($new_path)) {
						throw new DeployerException('Directory could not be created: '.$new_path, DeployerException::DIR_FAIL);
					}
					NedStars_Log::debug('Created dir: '.$new_path);
				}

				// move the files.
				// strip out the last folder. where creating it with the move command.
				NedStars_FileSystem::copyDir(
					$current_path,
					NedStars_FileSystem::popDir($new_path)
				);
			} else {
				NedStars_Log::warning('Folder not found: '.$current_path);
			}
		}

		// backup / preserve files
		foreach ($this->_config->preserve_data->files as $file_path) {
			if (is_file($this->_config->paths->web_live_path.'/'.$file_path)) {
				NedStars_FileSystem::copyFile(
					$this->_config->paths->web_live_path.'/'.$file_path,
					$this->_getSourceFolder().$file_path
				);
			} else {
				NedStars_Log::warning('File not found: '.$this->_config->paths->web_live_path.'/'.$file_path);
			}
		}

		//backup google*.htm file in live root.
		if ($this->_config->preserve_data->google_files) {
			if (is_dir($this->_config->paths->web_live_path)) {
				// TODO check if _getSourceFolder() works because of automated "/"
				NedStars_FileSystem::copyFilesByRegEx(
					'/^google(.*).htm/i',
					$this->_config->paths->web_live_path,
					$this->_getSourceFolder()
				);
			} else {
				NedStars_Log::warning('Google folder not found: '.$this->_config->paths->web_live_path);
			}
		}
	}

	/**
	 * Logic for clearing data from new installation
	 *
	 * @return void
	 */
	public function clearData() {
		NedStars_Log::message('Clearing out tmp data.');

		// clear out dir in temp new folder.
		foreach ($this->_config->clear_data->folders as $dir_path) {
			$temp_path = $this->_getSourceFolder().$dir_path;
			if (is_dir($temp_path)) {
				NedStars_FileSystem::deleteDirContent($temp_path);
			} else {
				NedStars_Log::warning('Folder not found: '.$temp_path);
			}
		}

		// clear out files in temp new folder.
		foreach ($this->_config->clear_data->files as $file_path) {
			$temp_file = $this->_getSourceFolder().$file_path;
			if (is_file($temp_file)) {
				unlink($temp_file);
			} else {
				NedStars_Log::warning('File not found: '.$temp_file);
			}
		}
	}

	/**
	 * Backup MySQL into tar in live dir
	 *
	 * @return void
	 */
	public function backupMysql() {
		if ($this->_config->backup->make_database_backup) {
			foreach ($this->_config->databases as $config_database) {
				foreach ($config_database->dbnames as $dbname) {
					$file = escapeshellarg($this->_config->paths->web_live_path.'/'.$config_database->host.'-'.$dbname.'.sql');
					NedStars_Log::message('Start MySQL backup via mysqldump to: '.$file);
					$command = 'mysqldump --user='.escapeshellarg($config_database->username);
					if ($config_database->password !== false) {
						$command .= ' --password='.escapeshellarg($config_database->password);
					}
					$command .= ' --host='.escapeshellarg($config_database->host);
					$command .= " --databases ".escapeshellarg($dbname);
					$command .= " --result-file=".$file;

					//force output or the function will not return the correct value.
					if (!NedStars_Execution::run($command." && echo 'OK'")) {
						throw new DeployerException('MySQL backup failed ('.$config_database->username.'@'.$config_database->host.':'.$dbname.').', DeployerException::MYSQL_FAIL);
					}
				}
			}
		} else {
			NedStars_Log::message('MySQL backup Skipped (Config value)');
		}
	}

	/**
	 * Backup live dir into tar
	 *
	 * @return void
	 */
	public function backupLive() {
		if ($this->_config->backup->make_file_backup) {
			$destination_file = $this->_config->backup->folder.'/backup_'.date('Ymd_Hi').'.tar.gz';
			NedStars_Log::message('Start backup live to : '.escapeshellarg($destination_file));
			NedStars_FileSystem::backupDir($this->_config->paths->web_live_path, $destination_file);
		} else {
			NedStars_Log::message('File backup Skipped (Config value)');
		}
	}

	/**
	 * Switch the live folder for the new one
	 *
	 * Clear temp_old_path dir
	 * Copy current live to temp_old_path dir.
	 * Copy new live from temp_new_path dir to live
	 * remove temp_new_path and temp_old_path
	 *
	 * @return void
	 */
	public function switchLive() {
		NedStars_Log::message('Switching live installation for new export.');
		NedStars_FileSystem::relocateDir(
			$this->_config->paths->web_live_path.'/',
			$this->_getSourceFolder(),
			$this->_config->paths->temp_old_path.'/'
		);

		NedStars_Log::message('Remove temporarily used directories.');
		if (is_dir($this->_config->paths->temp_new_path.'/')) {
			NedStars_FileSystem::deleteDir($this->_config->paths->temp_new_path.'/');
		}
		NedStars_FileSystem::deleteDir($this->_config->paths->temp_old_path.'/');
	}

	/**
	 * Send notification when backup is done
	 *
	 * @return void
	 */
	public function sendNotifications() {
		if (isset($this->_config->notifications) && is_object($this->_config->notifications)) {
			$project = preg_replace('/(.*):(.*).git/', '$2', $this->_config->archive->git->repo);
			$branch_name = $this->_config->archive->git->branch;

			$title = 'Deploy: '.$project;
			$message = '';
			$message .= 'Deployment made to for: '.$branch_name."\n";
			$message .= 'Host		: '.php_uname('n')."\n";
			$message .= 'Path		: '.$this->_config->paths->web_live_path."\n";
			$message .= 'Version	: '.self::VERSION."\n";
			$message .= 'Duration	: '.round((microtime(true) - $this->_time_start), 4)." seconds\n";

			Notification::notify($title, $message, $this->_config->notifications);
			NedStars_Log::message('Notifications send.');
		} else {
			NedStars_Log::message('No Notifications send (no recipients found).');
		}
	}

	/**
	 * Get data from Git
	 *
	 * @return void
	 */
	public function getSource() {
		switch(strtolower($this->_config->archive->type)) {
		case 'svn' :
			NedStars_Log::message('Get archive from SVN.');
			NedStars_Svn::getArchive(
				$this->_config->archive->svn->repo,
				$this->_config->archive->svn->username,
				$this->_config->archive->svn->password,
				$this->_config->paths->temp_new_path
			);

			break;
		case 'git' :
			NedStars_Log::message('Get archive from GIT.');
			NedStars_Git::getArchive(
				$this->_config->archive->git->repo,
				$this->_config->archive->git->branch,
				$this->_config->paths->temp_new_path,
				$this->_config->archive->git->source_folder
			);
			break;
		}
	}

	/**
	 * Set permisions to apache
	 *
	 * @return void
	 */
	public function setFolderPermisions() {
		NedStars_Log::debug('setPermisions: '.$this->_getSourceFolder().', '.$this->_config->permisions->user.', '.$this->_config->permisions->group);
		NedStars_FileSystem::chownDir(
			$this->_getSourceFolder(),
			$this->_config->permisions->user, $this->_config->permisions->group
		);

		NedStars_Log::debug('Making live installation read-only for relocation: '.$this->_config->paths->web_live_path);
		NedStars_FileSystem::chmodDir($this->_config->paths->web_live_path, '0400');
	}

	/**
	 * Verify if exec user is root
	 *
	 * @return void
	 */
	private function _verifyRootUser() {
		if (posix_geteuid() != 0) {
			throw new DeployerException('User must be root to execute this script.', DeployerException::NO_ROOT);
		} else {
			NedStars_Log::debug('User is root.');
		}
	}

	/**
	 * Delete old backups after N days
	 *
	 * @return void
	 */
	public function purgeOldBackups() {
		NedStars_Log::message('Purging backup files older than '.$this->_config->backup->retention_days.' days: '.$this->_config->backup->folder);
		NedStars_FileSystem::deleteOldFiles(
			$this->_config->backup->folder.'/',
			$this->_config->backup->retention_days
		);
	}


	/**
	 * Check if there is 4 times the used diskpace free
	 *
	 * @return Boolean
	 */
	private function _checkFreeDiskSpace() {
		if (is_dir($this->_config->paths->web_live_path)) {
			// curren web folder size (good indicator)
			$folder_size = NedStars_FileSystem::getDirectorySize($this->_config->paths->web_live_path);

			// live disk
			$free_size_live = disk_free_space($this->_config->paths->web_live_path);

			// times 4 beacuse if both on same disk then we need 3 times and a bit on margin.
			// one for new git checkout with data
			// one for backup (posibly on the same disk)
			// one for db backup (size unknown)
			if ($folder_size * 4 > $free_size_live) {
				throw new DeployerException('Not enough free disk space on Live.', DeployerException::DISK_SPACE);
			}

			// check backup dir if found
			if (is_dir($this->_config->backup->folder)) {
				// backup disk (could be on a other partition then the live)
				$free_size_backup = disk_free_space($this->_config->backup->folder);

				// also check if backup disk has enough free disk space for 1 backup
				if ($folder_size > $free_size_backup) {
					throw new DeployerException('Not enough free disk space on Backup.', DeployerException::DISK_SPACE);
				}
			}

			NedStars_Log::message('There is enough free disk space');
		} else {
			NedStars_Log::message('Disk space can not be checked');
		}
		return true;
	}

	/**
	 * Helper function to get the absoulte path for Archive source folder
	 * paths->temp_new_path + posible source folder
	 *
	 * @return String Abosulte path
	 */
	private function _getSourceFolder() {
		$path = null;
		switch(strtolower($this->_config->archive->type)) {
		case 'svn' :
			$path = NedStars_FileSystem::getNiceDir($this->_config->paths->temp_new_path);
			break;
		case 'git' :
			$path = NedStars_FileSystem::getNiceDir($this->_config->paths->temp_new_path .'/'.$this->_config->archive->git->source_folder);
			break;
		}
		return $path;
	}

	/**
	 * Helper function to check if all required binaries are present.
	 *
	 * @return void
	 * @throws DeployerException when binaries can not be found
	 */
	private function _checkBinaries() {
		$binaries = array();

		// SVN or GIT
		switch (strtolower($this->_config->archive->type)) {
		case 'svn' :
			$binaries[] = 'svn';
			break;
		case 'git' :
			$binaries[] = 'git';
			break;
		}

		// MYSQL dump
		if ($this->_config->backup->make_database_backup and !empty($this->_config->databases)) {
			$binaries[] = 'mysqldump';
		}

		// verify binaries
		$not_found_bin = NedStars_FileSystem::hasBinaries($binaries);
		if ($not_found_bin) {
			throw new DeployerException('Binaries '.implode(', ', $not_found_bin).' are not found.', DeployerException::BINARY_MISSING);
		}
	}
}
?>