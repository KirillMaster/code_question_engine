<?php

namespace App\Jobs;


use CodeQuestionEngine\CodeFileManagerFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use CalculateMarkDataContract;

class CheckResultJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * @var \Language
     */
    protected $language;

    /**
     * @var  \CodeFileManager
     */
    protected $fileManager;

    /**
     * @var array
     */
    protected $codeTasks;

    /**
     * Create a new job instance.
     *
     */

    public function __construct($lang, array $codeTasks)
    {
        $this->codeTasks = $codeTasks;
        $this->language = $lang;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->fileManager =  CodeFileManagerFactory::getCodeFileManager($this->language);
        $this->fileManager->setDirPath($this->codeTasks[0]->dirPath);
        $count  = count($this->codeTasks);
        echo "cases_count = $count\n";
        $mark = $this->fileManager->calculateMark($this->codeTasks);
        echo "оценка $mark\n";


        $contract  = new CalculateMarkDataContract();
        $contract->setMark($mark);
        $contract->setGivenAnswerId($this->codeTasks[0]->givenAnserId);

        //todo:: курлом вызывать внешнее апи


        foreach($this->codeTasks as $codeTask){
            echo $codeTask->key." почищена\n";
            $codeTask->delete();
        }

        return;
    }
}
