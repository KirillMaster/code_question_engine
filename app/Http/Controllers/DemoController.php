<?php


namespace App\Http\Controllers;

use CodeQuestionEngine\DockerInfo;
use CodeQuestionEngine\DockerManager;
use Repositories\UnitOfWork;
use TestCalculatorProxy;



class DemoController
{


    private $_uow;

    public function __construct(UnitOfWork $uow){

        $this->_uow = $uow;
    }
    public function docker(){



        //return TestCalculatorProxy::setAnswerMark(1,100);
    }
}