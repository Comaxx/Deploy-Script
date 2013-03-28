<?php
/**
 * Data HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Data HookInterface
 *
 * @project   NedStars Deployer
 * @category  Interfaces
 * @package   HookInterfaces
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
interface Hooks_DataInterface {

     /**
     * Pre hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preClearData(Deployer &$deployer);

    /**
     * Post hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postClearData(Deployer &$deployer);

    /**
     * Pre hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function prePreserveData(Deployer &$deployer);

    /**
     * Post hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postPreserveData(Deployer &$deployer);

    /**
     * Pre hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preSwitchLive(Deployer &$deployer);

    /**
     * Post hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postSwitchLive(Deployer &$deployer);

    /**
     * Pre hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function preSetFolderPermissions(Deployer &$deployer);

    /**
     * Post hook function
     *
     * @param Deployer &$deployer deployer object
     *
     * @return void
     */
    public function postSetFolderPermissions(Deployer &$deployer);



}
