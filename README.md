# penkins

WHAT PENKINS DOES:
-----------------

	- checks changes in GIT branch and copy/delete modified files via FTP
	- no web GUI , CLI only, no DB's used

REQUIREMENTS:
-------------

	PHP 8.2 and above, zip extension should be enabled in php.ini configuration file

HOW TO USE:
-----------

1. run manager-configuration-add.bat  to create new configuration for your .git branch
2. run manager-configuration-list.bat and manager-configuration-details.bat to check added configuration
3. run check.bat to add initial sha1 for your branch
4. after a chagnes in your .git branch run check.bat again 
5. run deploy.bat to copy/delete modified files  via FTP

6. you can create new .bat files with your configuration names to ommit configuration name typing every time you run check.bat or deploy.bat



