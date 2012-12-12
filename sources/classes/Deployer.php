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
	 * 5) Check if required binairies exist
	 * 6) Check for free disk space
	 * 7) Sanity_info: check if all configuration options are filled in
	 *    	Check if user is root
	 *    	Check if mysql and git credentials are present
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

		// 5) Check if required binairies exist
		$not_found_bin = NedStars_FileSystem::hasBinaries(array('git','mysqldump'));
		if ($not_found_bin) {
			throw new DeployerException('Binaries '.implode(', ', $not_found_bin).' are not found.', DeployerException::BINARY_MISSING);
		}

		// 6) Check for free disk space
		$this->_checkFreeDiskSpace();

		// 7) Sanity_info: check if all configuration options are filled in
		$this->_checkConfigurationValues();

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

		$config_database = $this->_config->database;

		if (empty($config_database->password)) {
			$password = NedStars_Execution::prompt('Enter MySQL password (' . $config_database->username . '@' . $config_database->host . ':' . $config_database->dbname . '): ', true);
			if (empty($password)) {
				$password = false;
			}
			$config_database->password = $password;
		}

		try {
			$connection = mysql_connect(
				$config_database->host,
				$config_database->username,
				$config_database->password
			);
			// throw exception if database could not be selected
			if (!mysql_select_db($config_database->dbname)) {
				throw new Exception('Database connection failed on dbname : '.$config_database->dbname);
			}

			// close connection after test
			mysql_close($connection);
			unset($connection);

		} catch (Exception $exception) {
			// register exception and rethrow.
			throw new DeployerException($exception->getMessage(), DeployerException::MYSQL_FAIL);
		}

		return true;
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
		NedStars_FileSystem::createDirIfNeeded($this->_config->backup->folder);

		// check if mysql credentials are ok.
		// setup a db connection to test credentials
		// but only if backup should be made
		if ($this->_config->backup->make_database_backup) {
			$this->_verifyMysqlCredentials();
		}

		// check if user is root
		$this->_verifyRootUser();

		// Check if git credentials are ok.
		switch(strtolower($this->_config->archive->type)) {
		case 'svn' :
			break;
		case 'git' :
			if (!NedStars_Git::verifyCredentials($this->_config->archive->git->repo, $this->_config->archive->git->branch)) {
				throw new DeployerException('Git credentials or branch are incorrect', DeployerException::GIT_CREDENTIALS);
			}
			break;
		default:
			throw new Exception('No archive type found: '.strtolower($this->_config->archive->type));
			break;
		}
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
			$new_path = NedStars_FileSystem::getNiceDir($this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder.'/'.$dir_path);

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
					$this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder.'/'.$file_path
				);
			} else {
				NedStars_Log::warning('File not found: '.$this->_config->paths->web_live_path.'/'.$file_path);
			}
		}

		//backup google*.htm file in live root.
		if ($this->_config->preserve_data->google_files) {
			if (is_dir($this->_config->paths->web_live_path)) {
				NedStars_FileSystem::copyFilesByRegEx(
					'/^google(.*).htm/i',
					$this->_config->paths->web_live_path,
					$this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder
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
			$temp_path = $this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder.'/'.$dir_path;
			if (is_dir($temp_path)) {
				NedStars_FileSystem::deleteDirContent($temp_path);
			} else {
				NedStars_Log::warning('Folder not found: '.$temp_path);
			}
		}

		// clear out files in temp new folder.
		foreach ($this->_config->clear_data->files as $file_path) {
			$temp_file = $this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder.'/'.$file_path;
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
			NedStars_Log::message('Start MySQL backup via mysqldump to: '.escapeshellarg($this->_config->paths->web_live_path."/database.sql"));
			$command = "mysqldump -u".($this->_config->database->username);
			if ($this->_config->database->password !== false) {
				$command .= " -p".($this->_config->database->password);
			}
			$command .= " -h".($this->_config->database->host);
			$command .= " --databases ".escapeshellarg($this->_config->database->dbname);
			$command .= " --result-file=".escapeshellarg($this->_config->paths->web_live_path."/database.sql");
			NedStars_Execution::run($command);
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
			$this->_config->paths->temp_new_path .'/'.$this->_config->archive->git->source_folder.'/',
			$this->_config->paths->temp_old_path.'/'
		);

		NedStars_Log::message('Remove temporarily used directories.');
		NedStars_FileSystem::deleteDir($this->_config->paths->temp_new_path.'/');
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
		NedStars_Log::debug('setPermisions: '.$this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder.', '.$this->_config->permisions->user.', '.$this->_config->permisions->group);
		NedStars_FileSystem::chownDir(
			$this->_config->paths->temp_new_path.'/'.$this->_config->archive->git->source_folder,
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
		$folder_size = NedStars_FileSystem::getDirectorySize($this->_config->paths->web_live_path);
		// live disk
		$free_size_live = disk_free_space($this->_config->paths->web_live_path);
		// backup disk (could be on a other partition then the live)
		$free_size_backup = disk_free_space($this->_config->backup->folder);

		// times 4 beacuse if both on same disk then we need 3 times and a bit on margin.
		// one for new git checkout with data
		// one for backup (posibly on the same disk)
		// one for db backup (size unknown)
		if ($folder_size * 4 > $free_size_live) {
			throw new DeployerException('Not enough free disk space on Live.', DeployerException::DISK_SPACE);
		}

		// also check if backup disk has enough free disk space for 1 backup
		if ($folder_size > $free_size_backup) {
			throw new DeployerException('Not enough free disk space on Backup.', DeployerException::DISK_SPACE);
		}

		NedStars_Log::message('There is enough free disk space');

		return true;
	}
}
?>