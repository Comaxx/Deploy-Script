<?php
/**
 * HookInterfaces_Deployer HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * HookInterfaces_Deployer HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
interface Hooks_DeployerInterface {

     /**
     * Pre hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preDeployer(Deployer &$deployer);

    /**
     * Post hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postDeployer(Deployer &$deployer);
}
