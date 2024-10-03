@echo off

:: this script copies latest or requested changes on ftp

echo ------------------------------
echo deploys selected or latest deployment file created by Checker
echo ------------------------------

set configuration=configuration1
set deployment=latest

set /p configuration=Enter name of configuration [%configuration%]:
set /p deployment=Enter name of deployment [%deployment%]:

echo ------------------------------

php cli.php  --controller=Deployer --configuration="%configuration%" --deployment="%deployment%"

pause