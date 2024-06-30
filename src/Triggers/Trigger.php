<?php

namespace Amghrby\Workflows\Triggers;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Amghrby\Workflows\DataBuses\DataBus;
use Amghrby\Workflows\DataBuses\DataBussable;
use Amghrby\Workflows\Fields\Fieldable;
use Amghrby\Workflows\Jobs\ProcessWorkflow;
use Amghrby\Workflows\Loggers\WorkflowLog;

class Trigger extends Model
{
    use DataBussable, Fieldable;

    protected $table = 'triggers';

    public string $family = 'trigger';

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

    public static array $output = [];
    public static array $fields = [];
    public static array $fields_definitions = [];

    protected $casts = [
        'data_fields' => 'array',
    ];

    public static array $commonFields = [
        'Description' => 'description',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function children(): MorphMany
    {
        return $this->morphMany('Amghrby\Workflows\Tasks\Task', 'parentable');
    }

    /**
     * Return Collection of models by type.
     *
     * @param  array  $attributes
     * @param  null  $connection
     */
    public function newFromBuilder($attributes = [], $connection = null): Trigger
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

    public function start(Model $model, array $data = []): void
    {
        $log = WorkflowLog::createHelper($this->workflow, $model, $this);
        $dataBus = new DataBus($data);

        try {
            $this->checkConditions($model, $dataBus);
        } catch (Exception $e) {
            $log->setError($e->getMessage(), $dataBus);
            exit;
        }

        ProcessWorkflow::dispatch($model, $dataBus, $this, $log);
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
}
