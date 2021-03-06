<?php

namespace App\Jobs;

use CodeQuestionEngine\CodeTask;
use CodeQuestionEngine\DockerManager;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;

class RunProgramJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var string
     */
    protected $command;



    /**
     * @var CodeTask
     */
    protected $codeTask;


    public function __construct($command,CodeTask $codeTask)
    {
        $this->command = $command;
        $this->codeTask = $codeTask;
    }


    public function handle(DockerManager $dockerManager)
    {
        try {
            $dockerManager->setLanguage($this->codeTask->language);
            $dockerInstance = $dockerManager->getOrCreateInstance();
            $this->codeTask->state = \CodeTaskStatus::Running;
            $this->codeTask->store();
            $dockerInstance->run($this->command);

            return;
        }
        catch(Exception $e){
            echo $e->getMessage();
            return;
        }
    }
}
