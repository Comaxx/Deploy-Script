<?php

class LaravelPostDeployHook implements Hooks_DeployerInterface {
        public function preDeployer(Deployer &$deployer) {
                // TODO: Implement preDeployer() method.
        }

        public function postDeployer(Deployer &$deployer) {
                NedStars_Log::message('Running Post Deploy commands:');

                $path = (string) $deployer->getConfig()->paths->web_live_path;

                $output = array();
                $return = 0;
                $command = 'cd "'.$path.'" ; php composer.phar dump-autoload -o';
                exec($command, $output, $return);
                NedStars_Log::message('Output: '.implode(PHP_EOL, $output));


                $output = array();
                $return = 0;
                $command = 'cd "'.$path.'" ; chmod -R 0777 app/storage';
                exec($command, $output, $return);
                NedStars_Log::message('Output: '.implode(PHP_EOL, $output));

        }

}
