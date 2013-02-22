<?php
/**
 * Deployer observer class
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */

/**
 * Deployer observer class
 *
 * @project   NedStars Deployer
 * @category  Convenience_Class
 * @package   Nedstars_Deployer
 * @author    Alain Lecluse, Nedstars <alain@nedstars.nl>
 * @copyright 2012  Nedstars <info@nedstars.nl>
 */
class DeployerObserver {
    
    private $_observers = array();
    
    /**
     * Add Observer to the list
     * 
     * @param Mixed $observer Observer 
     *
     * @return void
     */
    protected function attachObserver($observer) {
        $this->_observers[] = $observer;
    }
    
    /**
     * Trigger a hook call
     * 
     * @param String $trigger Name of the trigger {CLASS}_{FUNCTION}
     *
     * @return void
     */
    protected function  notify($trigger) {
        
        // seperate function and hook
        $parts = explode('_', $trigger, 2);
        // add "HookInterfaces_" to class name because of prefixing
        $hook = "Hooks_".$parts[0]."Interface";        
        $function_name = $parts[1];
        
        // TODO: make if fool proof
        foreach ($this->_observers as $obs) {
            if ($obs instanceof $hook) {
                $obs->$function_name($this);
            }
        }
    }
}