@echo off

:: this script copies latest or requested changes on ftp

echo ------------------------------
echo check changes in GIT branch from selected configuration
echo ------------------------------

set configuration=configuration1

set /p configuration=Enter name of configuration [%configuration%]:

echo ------------------------------

php cli.php  --controller=Checker --configuration="%configuration%"

pause