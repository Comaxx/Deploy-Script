<?php

class M2PostDeployHook implements Hooks_DeployerInterface
{
    public function preDeployer(Deployer &$deployer)
    {
        //TODO : if enabled
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento maintenance:enable";
        $this->runBashCommand($deployer, $bashCMD);
    }

    /**
     * @param Deployer $deployer
     */
    public function postDeployer(Deployer &$deployer)
    {
        NedStars_Log::message('Running Post Deploy commands:');
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php composer.phar install";
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento setup:upgrade 2>1 > /dev/null";
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento setup:di:compile 2>1 > /dev/null";
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento setup:static-content:deploy nl_NL en_US 2>1 > /dev/null";
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento maintenance:disable 2>1 > /dev/null"; //always disable maintainance mode
        $bashCMD[] = "/root/.phpbrew/php/php-7.0.14/bin/php bin/magento cache:flush 2>1 > /dev/null";

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
            $command = "cd {$path} && {$_bashCMD}";
            exec($command, $output, $return);
        }
        return $this;
    }

}
