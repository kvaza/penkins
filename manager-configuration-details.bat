@echo off

echo ------------------------------------------------------------
echo CONFIGURATION DETAILS
echo ------------------------------------------------------------

set name=configuration1

set /p name=Enter name of configuration [%name%]:

echo ------------------------------------------------------------

php cli.php  --controller=Manager --view=details --name="%name%"

pause