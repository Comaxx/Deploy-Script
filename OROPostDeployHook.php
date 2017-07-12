<?php

class OROPostDeployHook implements Hooks_DeployerInterface
{
    public function preDeployer(Deployer &$deployer)
    {
        //TODO : if enabled
        $this->runBashCommand($deployer, $bashCMD);
    }

    /**
     * @param Deployer $deployer
     */
    public function postDeployer(Deployer &$deployer)
    {
	    NedStars_Log::message('Running Post Deploy commands:');
        $bashCMD[] = "composer.phar install --prefer-dist 2>1 > /dev/null";
        $bashCMD[] = "app/console oro:migration:load --force --env=prod 2>1 > /dev/null";
        $bashCMD[] = "app/console oro:migration:data:load --env=prod 2>1 > /dev/null";
        $bashCMD[] = "app/console cache:clear --env=prod 2>1 > /dev/null";
	$bashCMD[] = "app/console cache:warmup --env=prod 2>1 > /dev/null";

        $this->runBashCommand($deployer, $bashCMD);
    }

    /**
     * @param Deployer $deployer
     * @param array $bashCMD
     * @return $this
     */
    protected function runBashCommand(Deployer &$deployer, array $bashCMD)
    {
        $path = (string)$deployer->getConfig()->paths->web_live_path;
        foreach ($bashCMD as $_bashCMD) {
            $output = array();
            $return = 0;
            $command = "cd {$path} && $(which php) {$_bashCMD}";
            exec($command, $output, $return);
        }
        return $this;
    }

}
