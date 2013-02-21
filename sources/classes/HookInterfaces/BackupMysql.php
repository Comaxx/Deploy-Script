<?php
/**
 * BackupMysql HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * BackupMysql HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
interface HookInterfaces_BackupMysql {
    
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
}

?>