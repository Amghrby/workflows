<?php

namespace Amghrby\Workflows\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Amghrby\Workflows\DataBuses\DataBus;
use Amghrby\Workflows\Loggers\WorkflowLog;
use Amghrby\Workflows\Triggers\Trigger;

class ProcessWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(protected Model $model, protected DataBus $dataBus, protected Trigger $trigger, protected WorkflowLog $log)
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        DB::beginTransaction();
        try {
            foreach ($this->trigger->children as $task) {
                $task->init($this->model, $this->dataBus, $this->log);
                $task->execute();
                $task->pastExecute();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->log->setError($e->getMessage(), $this->dataBus);
            $this->log->createTaskLogsFromMemory();
        }

        $this->log->finish();
        DB::commit();
    }
}
