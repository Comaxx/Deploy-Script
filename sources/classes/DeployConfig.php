<?php
/**
 * Deploy config class.
 *
 * @project   NedStars Deployer
 * @category  Nedstars_Config
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class DeployConfig {

	/**
	 * Configuration object version
	 */
	const VERSION = 1.3;

	/**
	 * Fix for phpmd, do not call this function.
	 * phpmd sees this functions as not used, but will be used in parseData
	 *
	 * @see self::parseData
	 *
	 * @return void
	 */
	private function _phpmd() {
		$this->_phpmd();
		$this->_newNode();
		$this->_checkLine();
		$this->_checkArray();
		$this->_loadDbFromFile();
	}

	/**
	 * Create a new node if not existing
	 *
	 * @param String $name node name
	 *
	 * @return DeployConfig for convenience
	 */
	private function _newNode($name) {
		if (!isset($this->$name)) {
			$this->$name = new DeployConfig();
		}

		return $this->$name ;
	}

	/**
	 * Adds single line value to config if value is set
	 * Last node in xPath is the name of the configuration value
	 *
	 * @param String   $xpath xpath to value in xml
	 * @param Resource $oXml  Simple xml object
	 *
	 * @return Mixed Value of element, could be preset by higher config.
	 */
	private function _checkLine($xpath, $oXml) {
		// element name
		$parts = explode('/', $xpath);
		$element_name = array_pop($parts);

		// create element if needed
		if (!isset($this->$element_name)) {
			$this->$element_name = null;
		}

		$xpath_result = $oXml->xpath($xpath);
		if (isset($xpath_result[0])) {
			$value = strval($xpath_result[0]);
			switch(strtolower(trim($value))) {
			case 'y':
			case 'yes':
			case 'true':
				$this -> $element_name = true;
				break;
			case 'n':
			case 'no':
			case 'false':
				$this -> $element_name = false;
				break;
			default :
				$this -> $element_name = trim($value);
				break;
			}

		}

		return $this -> $element_name;
	}

	/**
	 * Adds array lines to config if values are set
	 * Last node in xPath is repeating element.
	 * node before that is the name of the configuration value
	 *
	 * @param String   $xpath xpath to value in xml
	 * @param Resource $oXml  Simple xml object
	 *
	 * @return Mixed Value of element, could be preset by higher config.
	 */
	private function _checkArray($xpath, $oXml) {
		// part name
		$parts = explode('/', $xpath);
		$part_name = array_pop($parts);
		// rebuild xPath, the last part is repeating and is just pop of
		$xpath = implode('/', $parts);

		// get element name
		$element_name = array_pop($parts);

		// create element if needed
		if (!isset($this->$element_name)) {
			$this->$element_name = array();
		}

		$xpath_result = $oXml->xpath($xpath);
		// if element found
		if (isset($xpath_result[0]) && count($xpath_result[0]->$part_name) > 0) {

			// file elements
			foreach ($xpath_result[0]->$part_name as $element) {
				array_push($this->$element_name, strval($element));
			}
		}

		return $this -> $element_name;
	}

	/**
	 * Load databse info from local.xml for magento
	 *
	 * @param String $file_path Ablsolute path to file
	 *
	 * @return void
	 * @throws DeployerException When $file_path is not readable
	 */
	private function _loadDbFromFile($file_path) {
		if (!is_readable($file_path)) {
			throw new DeployerException($file_path.' does not exits or is not readable.', DeployerException::LOCAL_XML_FAILED);
		}

		$oXml = simplexml_load_file($file_path, 'SimpleXMLElement', LIBXML_NOCDATA);
		$this->host		= strval($oXml->global->resources->default_setup->connection->host);
		$this->username	= strval($oXml->global->resources->default_setup->connection->username);
		$this->dbname	= strval($oXml->global->resources->default_setup->connection->dbname);
		$this->password	= strval($oXml->global->resources->default_setup->connection->password);

		NedStars_Log::message('Loaded local MySQL config from: '.escapeshellarg($file_path));

		unset($oXml);
		unset($file_path);

	}

	/**
	 * Parse a configuration file for data
	 *
	 * @param DeployConfig &$config   Config object to fill
	 * @param String       $file_path Ablsolute path to file
	 *
	 * @return void
	 * @throws DeployerException When $file_path is not readable
	 */
	public static function parseData(DeployConfig &$config, $file_path) {
		if (!is_readable($file_path)) {
			throw new DeployerException($file_path.' does not exits or is not readable.', DeployerException::LOCAL_XML_FAILED);
		}

		$oXml = simplexml_load_file($file_path, 'SimpleXMLElement', LIBXML_NOCDATA);

		// check if configuration file is for current version
		if (strval($oXml['version']) != self::VERSION) {
			throw new DeployerException('Cofniguration version incorrect for: '.$file_path, DeployerException::CONFIG_FAIL);
		}

		// Prevent double loading of profiles, so save loaded profiles
		self::_addloadedConfigFile($config, $file_path);

		// check if we have a base profile.
		// everything below will be over writen
		$profile = strval($oXml->include_profile);

		if (!empty($profile)) {
			NedStars_Log::message('include_profile: '.$profile);
			DeployConfig::parseData($config, $profile);
		}

		// debug
		$config->_checkLine('//is_debug_modus', $oXml);

		// database
		$config->_newNode('database');
		$config->database->_checkLine('//database/database_config_file', $oXml);
		$config->database->_checkLine('//database/read_from_config', $oXml);
		$config->database->_checkLine('//database/host', $oXml);
		$config->database->_checkLine('//database/username', $oXml);
		$config->database->_checkLine('//database/dbname', $oXml);
		$config->database->_checkLine('//database/password', $oXml);

		//archive
		$config->_newNode('archive');
		$config->archive->_checkLine('//archive/type', $oXml);
		// git
		$config->archive->_newNode('git');
		$config->archive->git->_checkLine('//git/repo', $oXml);
		$config->archive->git->_checkLine('//git/branch', $oXml);
		$config->archive->git->_checkLine('//git/source_folder', $oXml);

		// svn
		$config->archive->_newNode('svn');
		$config->archive->svn->_checkLine('//archive/svn/repo', $oXml);
		$config->archive->svn->_checkLine('//archive/svn/branch', $oXml);
		$config->archive->svn->_checkLine('//archive/svn/source_folder', $oXml);

		// notifications
		$config->_newNode('notifications');
		$config->notifications->_checkArray('//notifications/email_addresses/address', $oXml);
		$config->notifications->_checkArray('//notifications/notifo_users/user', $oXml);
		$config->notifications->_checkArray('//notifications/pushover_users/user', $oXml);

		// paths
		$config->_newNode('paths');
		$config->paths->_checkLine('//paths/web_live_path', $oXml);
		$config->paths->_checkLine('//paths/temp_new_path', $oXml);
		$config->paths->_checkLine('//paths/temp_old_path', $oXml);

		// backup
		$config->_newNode('backup');
		$config->backup->_checkLine('//backup/folder', $oXml);
		$config->backup->_checkLine('//backup/retention_days', $oXml);
		$config->backup->_checkLine('//backup/make_database_backup', $oXml);
		$config->backup->_checkLine('//backup/make_file_backup', $oXml);

		// preserve_data
		$config->_newNode('preserve_data');
		$config->preserve_data->_checkArray('//preserve_data/folders/folder', $oXml);
		$config->preserve_data->_checkArray('//preserve_data/files/file', $oXml);
		$config->preserve_data->_checkLine('//preserve_data/google_files', $oXml);

		// clear_data
		$config->_newNode('clear_data');
		$config->clear_data->_checkArray('//clear_data/folders/folder', $oXml);
		$config->clear_data->_checkArray('//clear_data/files/file', $oXml);

		// permisions
		$config->_newNode('permisions');
		$config->permisions->_checkLine('//permisions/user', $oXml);
		$config->permisions->_checkLine('//permisions/group', $oXml);

		if (!empty($config->database->database_config_file)
			&& $config->database->read_from_config === true
			&& !empty($config->paths->web_live_path)
		) {
			$config->database->_loadDbFromFile(NedStars_FileSystem::getNiceDir($config->paths->web_live_path).$config->database->database_config_file);
		}
	}

	/**
	 * Prevent double loading of profiles, so save loaded profiles
	 *
	 * @param DeployConfig &$config   Config object to fill
	 * @param String       $file_path Ablsolute path to file
	 *
	 * @return void
	 */
	private static function _addloadedConfigFile(&$config, $file_path) {
		if (!isset($config->config_files)) {
			$config->config_files = array();
		}

		if (isset($config->config_files[$file_path])) {
			throw new DeployerException('Recursion detected on configuration profiles.', DeployerException::CONFIG_FAIL);
		} else {
			$config->config_files[$file_path] = $file_path;
		}
	}
}

?>