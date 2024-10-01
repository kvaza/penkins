<?php

/**
 * this script will copy/delete changes via ftp 'deployer'
 *
 * @version v.1.0.0 2018-04-11 ks
 * @package penkins
 * @example php cli.php  --controller=Deployer --configuration=config1 --deployment=latest
 */

namespace App\Controllers;

use \Exception;
use \ZipArchive;

use App\Services\CliController;
use App\Services\FtpDeployer;
use App\Services\GitChecker;
use App\Services\Penkins;

use App\Services\Exceptions\CliException;

class Deployer extends CliController
{
    public function run()
    {
        if(empty(self::$arguments['configuration'])) {
            throw new CliException('Unknown configuration.');
        }

        $penkins = new Penkins();
        $configuration = $penkins->findConfiguration(self::$arguments['configuration']);

        $penkins->setConfiguration($configuration);

        try {

            $penkins->setDeployment(self::$arguments['deployment'] ?? '');

            $this->display(['starting deployment', PHP_EOL]);
            $this->display([
                'repository: '. $configuration->repository,
                'branch: '. $configuration->branch,
                'ftp host: '. $configuration->ftp_host,
                'ftp path: '. $configuration->ftp_path,
                'deployment: '. (self::$arguments['deployment'] ?? Penkins::FILE_LATEST),
                PHP_EOL
            ]);

            $files = $penkins->getDeleteFiles();
            $path = $penkins->getArchivePath();

            $deployer = new FtpDeployer(
                $configuration->ftp_host,
                $configuration->ftp_login,
                $configuration->ftp_password,
                $configuration->ftp_path
            );

            foreach($files as $file){

                $this->display(['deleting: '. $file]);

                $r = $deployer->delete($file);
                if(!$r){
                    $this->display(['unable to delete: '. $file]);
                }
            }

            $zip = new ZipArchive();
            if ($zip->open($path, ZipArchive::RDONLY) === true) {

                $i =0;
                for($i; $i < $zip->count(); $i++) {

                    $stat = $zip->statIndex($i);
                    if(substr($stat['name'], -1) == '/'){ // directory
                        continue;
                    }

                    $zfh = $zip->getStreamIndex($i);

                    $this->display(['copying: '. $stat['name']]);
                    $deployer->copyByHandler($stat['name'], $zfh);

                    fclose($zfh);
                }
            }

            $zip->close();

        }catch(Exception $e) { // deployer exceptions are possible here

            throw new CliException($e->getMessage());
        }
    }
}
