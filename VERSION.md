Deploy Script 
========

Deploy Script 1.2
--------

- Added sub folder for profiles 
- Added wordpress profile (Wouter daan)
- Updated Magento profile
- Changed inner workings of verbose to quiet #6
- Replaced `$_SERVER['HOSTNAME']` with `php_uname('n')` #1
- Added script timer #2
- Added configuration option to make backups optional #3
- Fixed profile include recursion #4
- Fixed error when sending notifications with no receiver #7
- Fixed `clearData()` function to work properly #8
- Removed auto generated phpdoc


Deploy Script 1.1
--------

- Changed Exception handeling and Exceptions
- Added Notifications (email, pushover, notifo)
- Changed configuration to XML
- Added stackable XML configuration
- Added default concrete5 config
- Added local.xml read for database info (magento)
- Instalation manual
- Added how to
- changed version and usage output
- Added Exit code.

Deploy Script 1.0
--------

- Added locking mechanism
- Applyed new code standard
- Replaced use of shell_exec and exe for general pupouse call NedStars/Execution.php
- Add Free disk pace check (project size times 4)

Deploy Script 0.4
--------

- Added "deploy" as executable
- Added documentation
- Fixed check on git credentials
- Fixed check on existence of git branch / tag
- Improved Exception handeling
- Added Colors to output
- Added Version info

Deploy Script 0.3.3
--------

- Fixed preserv folder function

Deploy Script 0.3.2
--------

- Added $backup_retention_in_days=30 to deploy.conf.php.sample.
- Removed $log_file from deploy.conf.php.sample, no longer valid config argument. use --config.
- Fixed some phpDoc errors.
- Improved Readme file.

Deploy Script 0.3.1
--------

- Fixed sample config to include $clear_dirs and $clear_files.

Deploy Script 0.3
--------

- Rewriten code to PHP.
- Added general purpose classes.
- Clean up data at the end.

Deploy Script 0.2
--------

- Extend bash script to support magento, concrete 5, code igniter.