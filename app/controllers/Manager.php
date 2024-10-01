<?php

/**
 * this script will manage penkins configurations
 *
 * @version v.1.0.0 2024-04-0 ks
 * @package penkins
 * @example php cli.php  --controller=Manager --view=list
 * @example php cli.php  --controller=Manager --view=add --name=config1 --repository=/home/project1 --branch=web/trunk --ftp_host==site.com --ftp_login==login1 --ftp_password=pwd1 --ftp_path=public_html
 * @example php cli.php  --controller=Manager --view=details --name=config1
 * @example php cli.php  --controller=Manager --view=delete --name=config1
 */

namespace App\Controllers;

use App\Requests\ConfigurationRequest;

use App\Services\Penkins;
use App\Services\CliController;
use App\Services\Exceptions\CliException;

class Manager extends CliController
{
    private const DEFAULT_VIEW = 'list';

    public function run()
    {
        try{

            $view = self::$arguments['view'] ?? self::DEFAULT_VIEW;

            $method = 'view_'. $view;
            if(method_exists($this, $method)){

                $this->$method();
            }

        }catch(Exception $e) {

            throw new CliException($e->getMessage());
        }
    }

    /**
     * displays list of available configurations
     *
     */
    private function view_list()
    {
        $penkins = new Penkins();
        $configurations = $penkins->getConfigurations();

        self::log('configurations list with '. count($configurations). ' items returned');

        if(empty($configurations)){
            $this->display(['Nothing found.']);
        }else{

            foreach($configurations as $key => $value){
                $configurations[$key] = ($key+1). '. '. $value;
            }

            $this->display($configurations);
        }
    }

    /**
     * adds new configuration
     *
     */
    private function view_add()
    {
        $request = new ConfigurationRequest();
        $request->setFromArray(self::$arguments);

        if(!$request->isValid()){
            throw new CliException(join(PHP_EOL, $request->getErrorMessages()));
        }

        $penkins = new Penkins();
        $configurations = $penkins->addConfiguration($request);

        $this->display(['new configuration has been successfully added with name: '. $request->getValue('name')]);
    }

    /**
     * displays configuration details
     *
     */
    private function view_details()
    {
        $confname = self::$arguments['name'] ?? '';

        if(empty($confname)){
            throw new CliException('Unknown configuration.');
        }

        $penkins = new Penkins();
        $configuration = $penkins->findConfiguration($confname);

        $penkins->setConfiguration($configuration);

        $details = [
           'name: '. $configuration->name,
           'repository: '. $configuration->repository,
           'branch: '. $configuration->branch,
           'ftp host: '. $configuration->ftp_host,
           'ftp login: '. $configuration->ftp_login,
           'ftp password: '. $configuration->ftp_password,
           'ftp path: '. $configuration->ftp_path
        ];

        $files = $penkins->getDeploymentsFiles();

        $details[] = PHP_EOL;
        $details[] = 'deployments:';
        $details[] = PHP_EOL;

        $details = array_merge($details, $files);

        self::log('configuration details returned with name: '. self::$arguments['name']);
        $this->display($details);
    }

    /**
     * deletes configuration
     *
     */
    private function view_delete()
    {
        $name = self::$arguments['name'] ?? '';
        if(empty($name)){
            throw new CliException('Unknown name.');
        }

        $penkins = new Penkins();
        $configuration = $penkins->deleteConfiguration($name);

        $this->display(['configuration deleted with name: '. $name]);
    }
}
