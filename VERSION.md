Deploy Script
========

Deploy Script 1.4.3
--------
- Bugfix for copy symlink
- Added support for raw data in notification 


Deploy Script 1.4.3
--------
- Added support slack notifications
- Added support for also load configle filenames 


Deploy Script 1.4.2
--------
- Added support for http notifications

Deploy Script 1.4.1
--------
- Added support config info in hooks
- Added basic documentation on hooks

Deploy Script 1.4
--------
- Added support for Hooks #10
- Added extra debug info where missing
- Added support for initial deployments #31
- Added support for PHP 5.4
- Added support for symlinks
- Fixed execution path problems #27, it is now posible to run the script form remote path
- Fixed speed issues with chmod when user is allready oke.


Deploy Script 1.3
--------
- Added support for SVN #15, #22 and #23
- Added support for preserving data via regex
- Added support for multiple databases
- Added no password or ask password for MySQL
- Removed support for notification system "Notifo" #29
- Changed requirement for script to run as root. Now you need to run the script with  a user that has the right to the folders #30
- Auto fill Arhcive->type config value #28
- Fixed checks on backups #16 and #17
- Fixed TAR backup #14
- Fixed a issue with cleanup #11
- Improved notification info #13
- Disabled Mysqldumb binary check if it is not required
- Refactored config class



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
