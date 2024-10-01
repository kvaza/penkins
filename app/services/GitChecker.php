<?php

/**
 * Git checker works with git
 *
 * @version $Id: GitChecker.php v1.0.0 2012-12-18 12:00:00 ks $
 * @package penkins
 */

namespace App\Services;

use \stdClass;
use \Exception;

class GitChecker
{
    private const GIT_DIRECTORY = '.git';

    /**
     * commit id for selected branch
     *
     * @var string
     */
    private const GIT_COMMIT_SHA1_PATTERN = 'git --git-dir="%repository" rev-parse "%branch"';

    /**
     * returns differences between commits
     * @var string
     */
    //    private const GIT_DIFFS = 'git --git-dir="%s" diff-tree -r --no-commit-id --name-only --diff-filter=ACMRTD %s %s';
    private const GIT_DIFFS = 'git --git-dir="%repository" diff-tree -r  --diff-filter=ACMRTD %basesha1 %lastsha1';

    /**
     * show current branch
     */
    private const GIT_CURRENT_BRANCH = 'git --git-dir="%repository" rev-parse --abbrev-ref HEAD';

    /**
     * switches branch
     */
    private const GIT_ARCHIVE = 'git --git-dir="%repository" archive --format=zip --output=%output %sha1 -- %files';

    private $configuration = null;


    public function __construct(stdClass $configuration)
    {
        $this->configuration = $configuration;

        $this->configuration->repository .= '/'. self::GIT_DIRECTORY;
    }

    /**
     * returns last commitid for requested branch
     *
     * @return string
     */
    public function getCommitSha1()
    {
        $output = [];
        $code = 0;

        exec(
            str_replace(
                [
                    '%repository',
                    '%branch'
                ],
                [
                    $this->configuration->repository,
                    $this->configuration->branch
                ],
                self::GIT_COMMIT_SHA1_PATTERN,
            ),
            $output,
            $code
        );

        if($code){
            throw new Exception('Unable to get differences between: '. $basesha1. ' '. $lastsha1);
        }

        return $output[0] ?? '';
    }

    /**
     * returns list of modified files with their properties
     *
     * @throws Exception
     * @param string $basesha1
     * @param string $lastsha1
     * @return array [[sha1_from, sha1_to, action, name]]
     */
    public function getDiffs($basesha1, $lastsha1)
    {
        $output = [];
        $code = 0;

        exec(str_replace(
            [
                '%repository',
                '%basesha1',
                '%lastsha1'
            ],
            [
                $this->configuration->repository,
                $basesha1,
                $lastsha1
            ],
            self::GIT_DIFFS
            ),
            $output,
            $code
        );

        if($code){
            throw new Exception('Unable to get differences between: '. $basesha1. ' '. $lastsha1);
        }

        /*
        $output
        : array =
        0: string = ":100644 000000 e9a2d8ba57714741821fd7e687fe49f387f649e1 0000000000000000000000000000000000000000 D\tfil7.php"
        1: string = ":000000 100644 0000000000000000000000000000000000000000 e9a2d8ba57714741821fd7e687fe49f387f649e1 A\tfil777777.php"
        2: string = ":100644 000000 962381f9eac677b21964d0d34c73c8e8b911c45b 0000000000000000000000000000000000000000 D\tfile10.php"
        3: string = ":100644 100644 e440e5c842586965a7fb77deda2eca68612b1f53 d9ae4273acada5fc7435fe9bb5d8cc733bfae7bd M\tfile3.php"
        */

        $diffs = [];
        foreach($output as $line){

            $parts = explode("\t", $line);

            if(count($parts) < 2){
                continue;
            }

            $args = explode(' ', $parts[0]);

            $diffs[] = [
                'sha1_from' => $args[2],
                'sha1_to' => $args[3],
                'action' => $args[4],
                'file' => $parts[1]
            ];
        }

        return $diffs;
    }

    /**
     * packs requested files from requested commit
     *
     * @throws Exception
     * @param string $path path to archive
     * @param string $sha1
     * @param array $files list of files should be packed
     */
    public function createArchive($path, $sha1, array $files)
    {
        $output = [];

        $cmd = str_replace(
            [
                '%repository',
                '%output',
                '%sha1',
                '%files'
            ],
            [
                $this->configuration->repository,
                $path,
                $sha1,
                join(' ', $files)
            ],
            self::GIT_ARCHIVE
        );

        $code = 0;
        exec($cmd, $output, $code);

        if($code){
            throw new Exception('Unable to create archive.');
        }
    }
}
