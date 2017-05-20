<?php


namespace CodeQuestionEngine;
use MtHaml\Exception;
use Repositories\UnitOfWork;
use DockerInfo;

class DockerManager
{

    private $_uow;

    /**
     * @var DockerInfo
     */
    private $dockerInfo;

    private $appPath;

    /**
     * @var \Language
     */
    private $lang;

    public function __construct(UnitOfWork $_uow)
    {
        $this->appPath = app_path();
        $this->_uow = $_uow;
    }

    public function setLanguage($lang){
        $this->lang = $lang;
    }

    /**
     * Метод возвращает инстанс докера для конкретного языка
     * Если инстанса не существует, то он создается
     * @return DockerInstance
     */
    public function getOrCreateInstance(){

        $array_result = $this->_uow->dockerInfos()->findByLang($this->lang);

        if(count($array_result) == 0){

            $container_id  = $this->runDocker();
            $this->dockerInfo = $this->pushDockerInfo($container_id);
        }
        else{

            $this->dockerInfo = $array_result[0];

            $container_id = $this->dockerInfo->getContainerId();
            $instance = new DockerInstance();
            $instance->setContainerId($container_id);

            $instance = $this->createNewInstanceIfOldFalls($instance);


            return $instance;

        }

        $instance = new DockerInstance();
        $instance->setContainerId($this->dockerInfo->getContainerId());

        return $instance;
    }

    /**
     * @param DockerInstance $instance
     * @return DockerInstance
     * Создает новый докер, если старый по какой-то причине упал
     *
     */
    private function createNewInstanceIfOldFalls(DockerInstance $instance){

        $test_command = "echo test";

        $result_array = $instance->run($test_command);

        $result = "";
        if(count($result_array) > 0){
            $result = $result_array[0];
        }
        
        if($result != "test"){

            $container_id = $this->dockerInfo->getContainerId();
            $drop_command = "docker stop $container_id";

            exec($drop_command);
            $this->_uow->dockerInfos()->delete($this->dockerInfo);
            $this->_uow->commit();

            $container_id = $this->runDocker();

            $docker_info = $this->pushDockerInfo($container_id);
            $this->dockerInfo = $docker_info;
            $instance = new DockerInstance();
            $instance->setContainerId($this->dockerInfo->getContainerId());

        }
        return $instance;
    }
    private function runDocker(){
        error_reporting(E_ALL);
        ini_set('display_errors',1);

        exec("docker run -d -v $this->appPath/temp_cache:/opt/temp_cache -m 50M baseimage-ssh /sbin/my_init",$output);

        if(count($output) > 0 )
        {
            return $output[0];
        }
        else throw new Exception("Не удалось создать экземпляр виртуальной машины");
    }


    /**
     * убивает всех докеров
     */
    public function dropAllInstances(){

        $instances = $this->_uow->dockerInfos()->all();
        foreach($instances as $instance){

           $container_id =  $instance->getContainerId();
           $command = "docker stop $container_id";
           exec($command);
           $this->_uow->dockerInfos()->delete($instance);

        }
        $this->_uow->commit();

    }

    private function pushDockerInfo($container_id){
        $docker_info = new DockerInfo();
        $docker_info->setLang($this->lang);
        $docker_info->setContainerId($container_id);
        $this->_uow->dockerInfos()->create($docker_info);
        $this->_uow->commit();
        return $docker_info;

    }



}