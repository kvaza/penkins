@echo off

echo ------------------------------------------------------------
echo ADD NEW CONFIGURATION
echo ------------------------------------------------------------

set name=configuration1
set repository=/home/project1
set branch=web/trunk
set ftp_host=servername.com
set ftp_login=login1
set ftp_password=x123456
set ftp_path=public_html

set /p name=Enter name of configuration [%name%]:
set /p repository=Enter path to .git directory [%repository%]:
set /p branch=Enter name of tracking branch [%branch%]:
set /p ftp_host=Enter FTP Host [%ftp_host%]:
set /p ftp_login=Enter FTP Login [%ftp_login%]:
set /p ftp_password=Enter FTP Password [%ftp_password%]:
set /p ftp_path=Enter FTP Path [%ftp_path%]:

echo ------------------------------------------------------------

php cli.php  --controller=Manager --view=add --name="%name%" --repository="%repository%" --branch="%branch%" --ftp_host="%ftp_host%" --ftp_login="%ftp_login%" --ftp_password="%ftp_password%" --ftp_path="%ftp_path%"

pause