@echo off

echo ------------------------------------------------------------
echo DELETE CONFIGURATION
echo ------------------------------------------------------------

set name=configuration1

set /p name=Enter name of configuration [%name%]:

echo ------------------------------------------------------------

php cli.php  --controller=Manager --view=delete --name="%name%"

pause