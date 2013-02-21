<?php
/**
 * Backup hooks
 *
 * @project   NedStars Deployer
 * @category  Hooks
 * @package   Hooks
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Backup hooks
 *
 * @project   NedStars Deployer
 * @category  Hooks
 * @package   Hooks
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class Hooks_Backup implements HookInterfaces_BackupMysql, HookInterfaces_Deployer {
    
    /**
     * Pre hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preBackupMysql(Deployer &$deployer) {
        echo '<Pre> ', __CLASS__ , PHP_EOL;
    }
    
    /**
     * Post hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postBackupMysql(Deployer &$deployer) {
        echo '<Post> ', __CLASS__ , PHP_EOL;
    }
    
    /**
     * Pre hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preDeployer(Deployer &$deployer) {
        echo '<Pre> ', __CLASS__ , PHP_EOL;
    }
    
    /**
     * Post hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postDeployer(Deployer &$deployer) {
        echo '<Post> ', __CLASS__ , PHP_EOL;
    }
}

?>