<?php

/**
 * this script will check git branch for changes and create files for deployement
 *
 * @version v.1.0.0 2024-04-0 ks
 * @package penkins
 * @example php cli.php  --controller=Deployer --configuration=config1
 */

namespace App\Controllers;

use App\Services\CliController;
use App\Services\GitChecker;
use App\Services\Penkins;

use App\Services\Exceptions\CliException;

class Checker extends CliController
{
    public function run()
    {
        if(empty(self::$arguments['configuration'])) {
            throw new CliException('Unknown configuration.');
        }

        $penkins = new Penkins();
        $configuration = $penkins->findConfiguration(self::$arguments['configuration']);

        $penkins->setConfiguration($configuration);

        try{

            $checker = new GitChecker($configuration);
            $sha1 = $checker->getCommitSha1();

            $this->display([
                'branch: '. $configuration->branch,
                'sha1: '. $sha1
            ]);

            $deployments = $penkins->getDeployments();

            if(empty($deployments[0])){

                $penkins->storeDepoyments([$sha1]);
                $this->display(['first request data stored']);

            }elseif($deployments[0] != $sha1){ // get diffs

                $diffs = $checker->getDiffs($deployments[0], $sha1);

                $file = $penkins->storeDiffs($sha1, $diffs);

                $this->display([
                    count($diffs).' differences found:',
                    join(PHP_EOL, array_map(
                        function($v){
                            return $v['action']. ' '. $v['file'];
                        },
                        $diffs
                        )
                    ),
                    'file created: '. $file
                ]);

                $archive = $penkins->getArchivePath();

                $checker->createArchive(
                    $archive,
                    $sha1,
                    $penkins->getArchiveFiles($diffs)
                );

                $penkins->createLatestArchive();

                $this->display(['archive with modified files created: '. $archive]);

                array_unshift($deployments, $sha1);
                $penkins->storeDepoyments($deployments);

                $penkins->pruneDeployments();

            }else{

                $this->display(['changes not found']);
            }

        }catch(Exception $e) {

            throw new CliException($e->getMessage());
        }
    }
}
