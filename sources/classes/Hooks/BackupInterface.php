<?php
/**
 * Backup HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Backup HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
interface Hooks_BackupInterface {
    
     /**
     * Pre hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preBackupLive(Deployer &$deployer);
    
    /**
     * Post hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postBackupLive(Deployer &$deployer);
    
    /**
     * Pre hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preBackupMysql(Deployer &$deployer);
    
    /**
     * Post hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postBackupMysql(Deployer &$deployer);
    
    /**
     * Pre hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function prePurgeOldBackups(Deployer &$deployer);
    
    /**
     * Post hook function
     * 
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postPurgeOldBackups(Deployer &$deployer);
    
    
    
    
}

?>