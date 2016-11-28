<?php

class M2PostDeployHook implements Hooks_DeployerInterface
{
    public function preDeployer(Deployer &$deployer)
    {
        //TODO : if enabled
        $bashCMD[] = "./bin/magento maintenance:enable";
        $this->runBashCommand($deployer, $bashCMD);
    }

    /**
     * @param Deployer $deployer
     */
    public function postDeployer(Deployer &$deployer)
    {
        NedStars_Log::message('Running Post Deploy commands:');
        $bashCMD[] = "composer.phar install";
        $bashCMD[] = "./bin/magento setup:static-content:deploy nl_NL en_US";
        $bashCMD[] = "./bin/magento setup:upgrade";
        $bashCMD[] = "./bin/magento setup:di:compile";
        $bashCMD[] = "./bin/magento maintenance:disable"; //always disable maintainance mode
        $bashCMD[] = "./bin/magento cache:flush";

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
            $return = 0; //composer.phar install
            $command = "cd {$path} && $(which php) {$_bashCMD}";
            exec($command, $output, $return);
            NedStars_Log::message('Output: ' . implode(PHP_EOL, $output));
        }
        return $this;
    }

}
