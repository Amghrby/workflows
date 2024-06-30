<?php

namespace Amghrby\Workflows\Tasks;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Amghrby\Workflows\DataBuses\DataBus;
use Amghrby\Workflows\DataBuses\DataBussable;
use Amghrby\Workflows\Fields\Fieldable;
use Amghrby\Workflows\Loggers\TaskLog;
use Amghrby\Workflows\Loggers\WorkflowLog;
use Amghrby\Workflows\Workflow;

class Task extends Model implements TaskInterface
{
    use DataBussable, Fieldable;

    protected $table = 'tasks';

    public string $family = 'task';

    public $dataBus = null;
    public $model = null;
    public $workflowLog = null;

    protected $fillable = [
        'workflow_id',
        'parent_id',
        'type',
        'name',
        'data',
        'node_id',
        'pos_x',
        'pos_y',
    ];

    public static array $commonFields = [
        'Description' => 'description',
    ];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public static array $fields = [];
    public static array $output = [];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function parentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function children(): MorphMany
    {
        return $this->morphMany(__CLASS__, 'parentable');
    }

    /**
     * Return Collection of models by type.
     *
     * @param  array  $attributes
     * @param  null  $connection
     */
    public function newFromBuilder($attributes = [], $connection = null): self
    {
        $entryClassName = '\\'.Arr::get((array) $attributes, 'type');

        if (class_exists($entryClassName)
            && is_subclass_of($entryClassName, self::class)
        ) {
            $model = new $entryClassName();
        } else {
            $model = $this->newInstance();
        }

        $model->exists = true;
        $model->setRawAttributes((array) $attributes, true);
        $model->setConnection($connection ?: $this->connection);

        return $model;
    }


    /**
     * Check if all Conditions for this Action pass.
     *
     * @param Model $model
     * @param DataBus $data
     * @return bool
     * @throws Exception
     */
    public function checkConditions(Model $model, DataBus $data): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        $conditions = json_decode($this->conditions);

        foreach ($conditions->rules as $rule) {
            [$DataBus, $field] = explode('-', $rule->id);

            $result = $this->checkRule($model, $data, $DataBus, $field, $rule->operator, $rule->value);

            if (! $result) {
                throw new Exception("The Condition for Task {$this->name} with the field {$rule->field} {$rule->operator} {$rule->value} failed.");
            }
        }

        return true;
    }

    private function checkRule(Model $model, DataBus $data, string $DataBus, string $field, string $operator, $value): bool
    {
        $checkCondition = config('workflows.data_resources')[$DataBus]::checkCondition;

        return $checkCondition($model, $data, $field, $operator, $value);
    }

    /**
     * Initialize the task with the provided model, data, and workflow log.
     *
     * @param Model $model The model associated with the task
     * @param DataBus $data The data bus containing task data
     * @param WorkflowLog $log The log of the workflow
     * @throws Exception If an error occurs during initialization
     */
    public function init(Model $model, DataBus $data, WorkflowLog $log): void
    {
        $this->model = $model;
        $this->dataBus = $data;
        $this->workflowLog = $log;
        $this->workflowLog->addTaskLog($this->workflowLog->id, $this->id, $this->name, TaskLog::$STATUS_START, json_encode($this->data_fields), Carbon::now());

        $this->log = TaskLog::createHelper($log->id, $this->id, $this->name);

        $this->dataBus->collectData($model, $this->data_fields);

        $this->checkConditions($model, $this->dataBus);
    }

    /**
     * Execute the Action return Value tells you about the success.
     *
     * @return void
     */
    public function execute(): void
    {
    }

    public function pastExecute(): void
    {
        if (empty($this->children)) {
            return;
        }
        $this->log->finish();
        $this->workflowLog->updateTaskLog($this->id, '', TaskLog::$STATUS_FINISHED, Carbon::now());
        foreach ($this->children as $child) {
            $child->init($this->model, $this->dataBus, $this->workflowLog);
            try {
                $child->execute();
            } catch (\Throwable $e) {
                $child->workflowLog->updateTaskLog($child->id, $e->getMessage(), TaskLog::$STATUS_ERROR, Carbon::now());
                throw $e;
            }
            $child->pastExecute();
        }
    }
}
