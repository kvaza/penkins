<?php

/**
 * main penkins service
 *
 * @version $Id: Penkins.php v1.0.0 2012-12-18 12:00:00 ks $
 * @package penkins
 */

namespace App\Services;

use \Exception;
use \stdClass;

use App\Requests\ConfigurationRequest;

class Penkins
{
    public const FILE_LATEST = 'latest';

    private const DEPLOYMENT_NAME_PATTERN = '%s-%s';

    private const FLAG_DELETE = 'D';

    private const DEPLOYMENT_EXTENSION = 'txt';
    private const ARCHIVE_EXTENSION = 'zip';

    private const FILE_CONFIGURATION = 'configuration.json';
    private const FILE_DEPLOYMENTS = 'deployments.json';

    private const DIR_DEPLOYMENTS = 'deployments';
    private const DIR_FILES = 'files';

    /**
     * how many deployments allowed to be stored on disk
     */
    private const LIMIT_DEPLOYMENTS = 500;

    /**
     * configuration file structure
     *
     * @var array
     */
    public $configurationDefault = [
        'path' => '',
        'name' => '',
        'repository' => '',
        'branch' => '',
        'ftp_host' => '',
        'ftp_login' => '',
        'ftp_password' => '',
        'ftp_path' => ''
    ];

    /**
     * current configuration
     *
     * @var stdClass
     */
    public $configuration = null;

    /**
     * name of current deployment
     *
     * @var string
     */
    private $deployment = '';

    public function __construct()
    {

    }

    /**
     * returns list of ocnfigurations
     *
     * @return array [name1, name2]
     */
    public function getConfigurations()
    {
        $configurations = [];

        $dh = opendir(DIR_CONFIGURATIONS);
        while($dn = readdir($dh)){

            if($dn != '.' and $dn != '..' and is_file($f = DIR_CONFIGURATIONS. '/'. $dn. '/'. self::FILE_CONFIGURATION)) {

                $jdata = json_decode(file_get_contents($f));
                if(!empty($jdata->name)) {

                    $configurations[] = $jdata->name;
                }
            }
        }
        closedir($dh);

        return $configurations;
    }

    /**
     * creates new configuration
     *
     * @throws Exception
     * @param ConfigurationRequest $request
     */
    public function addConfiguration(ConfigurationRequest $request)
    {
        $configuration = [];

        $dir = $this->getConfigurationDir($request->getValue('name'));
        if(is_dir($dir)){ // create dir
            throw new Exception('Configuration already exists with name: '. $request->getValue('name'));
        }


        foreach($this->configurationDefault as $key => $value){
            $configuration[$key] = $request->getValue($key);
        }

        $configuration['path'] = $dir;

        mkdir($dir);
        mkdir($dir. '/'. self::DIR_DEPLOYMENTS);
        mkdir($dir. '/'. self::DIR_FILES);

        file_put_contents($dir. '/'.  self::FILE_CONFIGURATION, json_encode($configuration));
        file_put_contents($dir. '/'.  self::FILE_DEPLOYMENTS, json_encode([]));
    }

    public function storeDepoyments(array $deployments)
    {
        file_put_contents($this->configuration->path. '/'.  self::FILE_DEPLOYMENTS, json_encode($deployments));
    }

    /**
     * returns ocnfiguration details
     *
     * @param string $name name of configuration
     * @return stdClass {name, repository, branch, ftp_host, ftp_login, ftp_password, ftp_path}
     */
    public function findConfiguration($name)
    {
        $dir = $this->getConfigurationDir($name);
        if(empty($dir) or !is_dir($dir)){
            throw new Exception('Configuration not found with name: '. $name);
        }

        $jdata = file_get_contents($dir. '/'.  self::FILE_CONFIGURATION);
        return json_decode($jdata);
    }

    /**
     * sets current configuration required for a some methods
     *
     * @param stdClass $configuration
     */
    public function setConfiguration(stdClass $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * returns list of deployments
     *
     * @return array
     */
    public function getDeployments()
    {
        $jdata = file_get_contents($this->configuration->path. '/'.  self::FILE_DEPLOYMENTS);
        return json_decode($jdata);
    }

    /**
     * returns list of avaiable deployments files
     *
     * @return array
     */
    public function getDeploymentsFiles()
    {
        $files = [];

        $path = $this->configuration->path. '/'.  self::DIR_DEPLOYMENTS;
        $dh = opendir($path);
        while($dn = readdir($dh)){

            if($dn != '.' and $dn != '..') {

                $files[] = $dn;
            }
        }

        closedir($dh);
        rsort($files);

        return $files;
    }

    /**
     * store only limited number of deployments
     *
     */
    public function pruneDeployments()
    {
        $files = array_slice($this->getDeploymentsFiles(), self::LIMIT_DEPLOYMENTS);
        foreach($files as $file){

             unlink( $this->configuration->path. '/'.  self::DIR_DEPLOYMENTS. '/'. $file);

             $archive = str_replace(self::DEPLOYMENT_EXTENSION, self::ARCHIVE_EXTENSION, $file);
             if(is_file($this->configuration->path. '/'.  self::DIR_FILES. '/'. $archive)){

                 unlink( $this->configuration->path. '/'.  self::DIR_FILES. '/'. $archive);
             }
        }
    }

    /**
     * removes configuration
     *
     * @param string $name
     */
    public function deleteConfiguration($name)
    {
        $dir = $this->getConfigurationDir($name);
        if(!is_dir($dir)){
            throw new Exception('Configuration not found with name: '. $name);
        }

        unlink($dir. '/'.  self::FILE_CONFIGURATION);
        unlink($dir. '/'.  self::FILE_DEPLOYMENTS);

        $this->deleteFiles($dir. '/'. self::DIR_DEPLOYMENTS);
        $this->deleteFiles($dir. '/'. self::DIR_FILES);

        rmdir($dir. '/'. self::DIR_DEPLOYMENTS);
        rmdir($dir. '/'. self::DIR_FILES);
        rmdir($dir);
    }

    private function getConfigurationDir($name)
    {
        return DIR_CONFIGURATIONS. '/'. preg_replace('/[^a-z0-9_-]/i', '', strtolower($name));
    }

    /**
     * removes files from directory
     *
     * @param string $dir
     * @return number of files deleted
     */
    private function deleteFiles($dir)
    {
        $n = 0;
        if(!is_dir($dir)){
            return $n;
        }

        $dh = opendir($dir);
        while($dn = readdir($dh)){

            if($dn != '.' and $dn != '..') {
                unlink($dir. '/'. $dn);
                $n++;
            }
        }
        closedir($dh);

        return $n;
    }

    /**
     * stores diffs
     *
     * @param string $sha1 sha1
     * @param array $diffs array [[sha1_from, sha1_to, action, file]]
     * @return path to deployemnt file
     */
    public function storeDiffs($sha1, array $diffs)
    {
         $lines = [];

         foreach($diffs as $value){
            $lines[] = join("\t", $value);
         }

         $path = $this->configuration->path. '/'.  self::DIR_DEPLOYMENTS;
         $this->deployment = $this->getDeploymentName($sha1);

         $file = $this->deployment. '.'. self::DEPLOYMENT_EXTENSION;

         file_put_contents($path. '/'. $file, join(PHP_EOL, $lines));
         copy($path. '/'. $file, $path. '/'. self::FILE_LATEST. '.'. self::DEPLOYMENT_EXTENSION);

         return $path. '/'. $file;
    }

    public function getArchiveFiles(array $diffs)
    {
         $files = [];
         foreach($diffs as $value){

             if(($value['action'] ?? '') != self::FLAG_DELETE){
                $files[]= $value['file'];
             }
         }

         return $files;
    }

    /**
     * create deployment name
     *
     * @param string $sha1
     * @return string
     */
    private function getDeploymentName($sha1)
    {
        return sprintf(self::DEPLOYMENT_NAME_PATTERN, date('Ymd-His'), substr($sha1, 0, 8));
    }

    /**
     * sets current deployment
     *
     * @throws Exception throws exception if deployment is missing
     * @param string $deployment
     *
     */
    public function setDeployment($deployment)
    {
        if(empty($deployment)) {
            $deployment = self::FILE_LATEST;
        }

        $file = $this->configuration->path. '/'. self::DIR_DEPLOYMENTS. '/'. $deployment. '.'. self::DEPLOYMENT_EXTENSION;

        if(!is_file($file)){
            throw new Exception('Unknown deployment requested.');
        }

        $this->deployment = $deployment;
    }

    /**
     * returns list of files for deletion
     *
     * @return array
     */
    public function getDeleteFiles()
    {
        $files = [];

        $fh = fopen($this->configuration->path. '/'. self::DIR_DEPLOYMENTS. '/'. $this->deployment. '.'. self::DEPLOYMENT_EXTENSION, 'rb');
        while(!feof($fh)) {

            // e9a2d8ba57714741821fd7e687fe49f387f649e1\t0000000000000000000000000000000000000000\tD\tfile7.php
            $parts = explode("\t", trim(fgets($fh)));
            if($parts[2] == self::FLAG_DELETE) {

                $files[] = $parts[3];
            }
        }

        fclose($fh);
        return $files;
    }

    /**
     * returns path to archive with file for copying
     *
     * @return string
     */
    public function getArchivePath()
    {
        return $this->configuration->path. '/'. self::DIR_FILES. '/'.  $this->deployment. '.'. self::ARCHIVE_EXTENSION;
    }

    public function createLatestArchive()
    {
        copy($this->getArchivePath(), $this->configuration->path. '/'. self::DIR_FILES. '/'.  self::FILE_LATEST. '.'. self::ARCHIVE_EXTENSION);
    }
}
