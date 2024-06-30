<?php

namespace Amghrby\Workflows\Loggers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Amghrby\Workflows\DataBuses\DataBus;
use Amghrby\Workflows\Triggers\WorkflowObservable;
use Amghrby\Workflows\Workflow;
use Amghrby\Workflows\Loggers\TaskLog;

class WorkflowLog extends Model
{
    use WorkflowObservable;

    protected $table = 'workflow_logs';

    public static $STATUS_START = 'start';
    public static $STATUS_FINISHED = 'finished';
    public static $STATUS_ERROR = 'error';

    private array $taskLogsArray = [];

    protected array $dates = [
        'start',
        'end',
    ];

    protected $fillable = [
        'workflow_id',
        'name',
        'status',
        'message',
        'start',
        'elementable_id',
        'elementable_type',
        'triggerable_id',
        'triggerable_type',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function taskLogs(): HasMany
    {
        return $this->hasMany(TaskLog::class);
    }

    public function elementable(): MorphTo
    {
        return $this->morphTo();
    }

    public function triggerable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function createHelper(Model $workflow, Model $element, $trigger): WorkflowLog
    {
        return self::create([
            'workflow_id' => $workflow->id,
            'name' => $workflow->name,
            'elementable_id' => $element->id,
            'elementable_type' => get_class($element),
            'triggerable_id' => $trigger->id,
            'triggerable_type' => get_class($trigger),
            'status' => self::$STATUS_START,
            'message' => '',
            'start' => Carbon::now(),
        ]);
    }

    public function setError(string $errorMessage, DataBus $dataBus): void
    {
        $this->message = $errorMessage;
        $this->status = self::$STATUS_ERROR;
        $this->end = Carbon::now();
        $this->save();
    }

    public function finish(): void
    {
        $this->status = self::$STATUS_FINISHED;
        $this->end = Carbon::now();
        $this->save();
    }

    public function addTaskLog(int $workflow_log_id, int $task_id, string $task_name, string $status, string $message, $start, $end = null): void
    {
        $this->taskLogsArray[$task_id] = [
            'workflow_log_id' => $workflow_log_id,
            'task_id' => $task_id,
            'task_name' => $task_name,
            'status' => $status,
            'message' => $message,
            'start' => $start,
            'end' => $end,
        ];
    }

    public function updateTaskLog(int $task_id, string $message, string $status, \DateTime $end): void
    {
        $this->taskLogsArray[$task_id]['message'] = $message;
        $this->taskLogsArray[$task_id]['status'] = $status;
        $this->taskLogsArray[$task_id]['end'] = $end;
    }

    public function createTaskLogsFromMemory(): void
    {
        foreach ($this->taskLogsArray as $taskLog) {
            TaskLog::updateOrCreate(
                [
                    'workflow_log_id' => $taskLog['workflow_log_id'],
                    'task_id' => $taskLog['task_id'],
                ],
                [
                    'name' => $taskLog['task_name'],
                    'status' => $taskLog['status'],
                    'message' => $taskLog['message'],
                    'start' => $taskLog['start'],
                    'end' => $taskLog['end'],
                ]
            );
        }
    }
}
