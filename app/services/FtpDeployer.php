<?php

/**
 * FTP writer,
 *  - copies files to ftp
 *  - deletes files from the list
 *
 * @version $Id: FtpDeployer.php v1.0.0 2012-12-18 12:00:00 ks $
 * @package penkins
 */

namespace App\Services;

use \Exception;

class FtpDeployer
{
    private $connection = null;

    /**
     * list of known dirs
     *
     * @var array
     */
    private $dirs = [];

    public function __construct($host, $login, $password, $path, $passivemode = true)
    {
        $this->connection = ftp_connect($host);

        if(empty($this->connection)){
            throw new Exception('Couldn\'t connect to: '. $host);
        }

        if(!@ftp_login($this->connection, $login, $password)){
            throw new Exception('Couldn\'t login as: '. $login);
        }

        if(!empty($path)){

            if(!@ftp_chdir($this->connection, $path)){
                throw new Exception('Couldn\'t change directory to: '. $path);
            }
        }

        ftp_pasv($this->connection, $passivemode);
    }

    /**
     * delete file
     *
     * @param string $file
     * @return bool
     */
    public function delete($file)
    {
        $size = ftp_size($this->connection, $file);
        if($size > -1){
            return @ftp_delete($this->connection, $file);
        }else{
            return false;
        }
    }

    /**
     * copies files from archive
     *
     * @param string $name name of file with full path
     * @param resource $zfh file handler
     * @return bool
     */
    public function copyByHandler($name, $zfh)
    {
         $parts = explode('/', $name);
         $file = array_pop($parts); // file at the end of list

         $this->makeDirs($parts);

         if(!@ftp_fput($this->connection, $name, $zfh, FTP_BINARY)){
             return false;
         }

         return true;
    }

    private function makeDirs($dirs)
    {
         $path = './'. join('/', $dirs). '/';

         if(in_array($path, $this->dirs)) { // known path, already was checked and created
             return;
         }

         $path = './';

         foreach($dirs as $dir){

            $nlist = ftp_nlist($this->connection, $path);
            if(!in_array($path. $dir, $nlist)){

                if(!@ftp_mkdir($this->connection, $path. $dir)){
                    return false;
                }
            }

            $path .= $dir. '/';
         }

         $this->dirs[] = $path;
    }
}
