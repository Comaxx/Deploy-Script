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

/**
 * Deploy config object.
 *
 * @project   NedStars Deployer
 * @category  Nedstars_Config
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class DeployConfig {
	
	/**
	 * Create a new node if not existing
	 * 
	 * @param String $name node name
	 *
	 * @return DeployConfig for convenience
	 */
	public function newNode($name) {
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
	public function checkLine($xpath, $oXml) {
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
	public function checkArray($xpath, $oXml) {
		// part name
		$parts = explode('/', $xpath);
		$part_name = array_pop($parts);
		// rebuild xPath, the last part is repeating and is just pop of
		$xpath = implode('/', $parts);
		
		// element name
		$parts = explode('/', $xpath);
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
	public function loadDbFromFile($file_path) {
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
	public static function parseData(&$config, $file_path) {
		if (!is_readable($file_path)) {
			throw new DeployerException($file_path.' does not exits or is not readable.', DeployerException::LOCAL_XML_FAILED);
		}
		
		$oXml = simplexml_load_file($file_path, 'SimpleXMLElement', LIBXML_NOCDATA);
		// check if we have a base profile.
		// everything below will be over writen
		$profile = strval($oXml->include_profile);
		
		if (!empty($profile)) {
			NedStars_Log::message('include_profile: '.$profile);
			DeployConfig::parseData($config, $profile);
		}
		
		// debug
		$config->checkLine('//is_debug_modus', $oXml);
		
		// database
		$config->newNode('database');
		$config->database->checkLine('//database/database_config_file', $oXml);
		$config->database->checkLine('//database/read_from_config', $oXml);	
		$config->database->checkLine('//database/host', $oXml);
		$config->database->checkLine('//database/username', $oXml);
		$config->database->checkLine('//database/dbname', $oXml);
		$config->database->checkLine('//database/password', $oXml);
		
		// git
		$config->newNode('git');
		$config->git->checkLine('//git/repo', $oXml);
		$config->git->checkLine('//git/branch', $oXml);
		$config->git->checkLine('//git/source_folder', $oXml);
		
		// notifications
		$config->newNode('notifications');
		$config->notifications->checkArray('//notifications/email_addresses/address', $oXml);
		$config->notifications->checkArray('//notifications/notifo_users/user', $oXml);
		$config->notifications->checkArray('//notifications/pushover_users/user', $oXml);
		
		// paths
		$config->newNode('paths');
		$config->paths->checkLine('//paths/web_live_path', $oXml);
		$config->paths->checkLine('//paths/temp_new_path', $oXml);
		$config->paths->checkLine('//paths/temp_old_path', $oXml);
		
		// backup
		$config->newNode('backup');
		$config->backup->checkLine('//backup/folder', $oXml);
		$config->backup->checkLine('//backup/retention_days', $oXml);
		
		// preserve_data
		$config->newNode('preserve_data');
		$config->preserve_data->checkArray('//preserve_data/folders/folder', $oXml);
		$config->preserve_data->checkArray('//preserve_data/files/file', $oXml);
		$config->preserve_data->checkLine('//preserve_data/google_files', $oXml);
		
		// clear_data
		$config->newNode('clear_data');
		$config->clear_data->checkArray('//clear_data/folders/folder', $oXml);
		$config->clear_data->checkArray('//clear_data/files/file', $oXml);
		
		// permisions
		$config->newNode('permisions');
		$config->permisions->checkLine('//permisions/user', $oXml);
		$config->permisions->checkLine('//permisions/group', $oXml);
		
		if (!empty($config->database->database_config_file)
			&& $config->database->read_from_config === true
			&& !empty($config->paths->web_live_path)
		) {
			$config->database->LoadDbFromFile(NedStars_FileSystem::getNiceDir($config->paths->web_live_path).$config->database->database_config_file);
		}
	}
}

?>