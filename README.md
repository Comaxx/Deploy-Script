Deploy Script
===================

The deploy script is a tool to easily deploy new releases of your webbased applications.
It's goal is to grab the new source code from your versioning system (Git or Subversion) and safely deploy it to a (production) environment.
Functionality included are things like:

- Perform a database backup prior to deployment
- Performa a file backup prior to deployment
- Send notifications via e-mail or Pushover after deployment
- Hot swap to new code base, so there's virtually no downtime


Available subcommands are:
----

  		--config <name>		Will use configuration file deploy.<name>.conf.php. default file: deploy.conf.php.
  		--tag <tag #>		Git/SVN tag to be deployed.
  		--branch <branch>	Git/SVN branch to be deployed.
  		--debug				Debug mode: default = false.
  		--quiet				Quiet mode, only output warnings and exceptions, only if debug is not given: default = false.
  		--version			Shows version information
  		-c <name>			Alias for --config.
  		-t <tag #>			Alias for --tag.
  		-b <branch>			Alias for --branch.
  		-d					Alias for --debug.
  		-q					Alias for --quiet.

Installation
----
- Grab project from GIT
- Copy the example file for your project from sources/.. to the root of the project.
- Fill in the configuration where still blank


###Installation Concrete 5
Log into the server and grab a copy of the script.

	$ clone git://github.com/Comaxx/Deploy-Script.git

Copy the example file for each enviroment that your need, for example "staging" or "live", from sources/.. to the root of the project.
When executing ./deploy the -c or --config argument is used to specify which config should be loaded.
When no explicit configuration is given the default one is used (deploy.conf.xml)

	$ cp sources/example.concrete5.conf.xml <config_name>.conf.xml

Fill in the <config_name>.conf.xml file where still blank.

- Database connection data or use local.xml loader.
- Git repo, deploy script uses git --archive to get the source code from the repo for your deployment
- Notifications, configure how to notify peopele when deployemnt is done.
- Paths, there are 4 folders that need to be configured
	- live path, path to project folder (the one that will be updated)
	- temp new path, this is the folder where the new source will be build up.
	- temp old path, when switching to the new source the old source is copied to this folder
	- backup path, folder where all backups will be played

Execute example

	$ ./deploy -c <config> -t <tag number>

Notifications
----
There are three types of notification services:

- E-mail
- Pushover (https://pushover.net/)
- HTTP

The XML looks like

	<notifications>
		<email_addresses>
			<address>example@example.com</address>
		</email_addresses>
		<pushover_users>
			<user>183SSd882exampleS82</user>
		</pushover_users>
		<http_addresses>
			<url>http://api.example.com/</url>
		</http_addresses>
	</notifications>

Maintenance page
----
To speed up the process of a database backup it is advisable to stop all traffic to a website before that step. 
For that purpose you may use the following steps to ensure a maintenance page is shown as a replacement for any and all requests to the live environment.

1. You'll need the following in your .htaccess file or the equivelant in your server configuration

```
ErrorDocument 503 /maintenance.html
RewriteCond %{REQUEST_URI} !\.(css|gif|jpg|png)$
RewriteCond %{DOCUMENT_ROOT}/maintenance.html -f
RewriteCond %{SCRIPT_FILENAME} !maintenance.html
RewriteRule ^.*$ - [redirect=503,last]
```

2. Tell the script which file to use as the maintenance page in your deploy configuration

```
<maintenance>
	<template>_maintenance.html</template>
	<deploy>maintenance.html</deploy>
</maintenance>
```

The deploy script will copy the template to the location it should have during deployment (paths are relative from the root of your environment). Due 
to the server configuration this will result in a redirect to the file as long as it exists. The file will also be present in the backup, which allows 
you to place the code back, and then import the database dump in case of failure, while the website is in maintenance mode. 

The maintenance page can be safely removed manually at any point if needed, since it is copied, rather than moved. An example page is included.
	
Hooks
----
There are 5 hook groups

- Data
- Backup
- Notifications
- Source
- Deployer

the classname and file name should be the same if you want to implement a hook
classname: Hooks_{{group}}Interface
filename: Hooks_{{group}}Interface.php

The XML looks like

	<hooks>
		<files>
			<file>Hooks_DataInterface.php</file>
			<file>Hooks_SourceInterface.php</file>
		</files>
	</hooks>
