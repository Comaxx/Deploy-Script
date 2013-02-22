<?php
/**
 * BackupLive HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * BackupLive HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
interface HookInterfaces_BackupLive {
    
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
}

?>