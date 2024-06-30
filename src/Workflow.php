<?php

namespace Amghrby\Workflows;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Amghrby\Workflows\Triggers\Trigger;

class Workflow extends Model
{
    private $data;

    protected $table = 'workflows';

    protected $fillable = [
        'name',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflows.db_prefix').$this->table;
        parent::__construct($attributes);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany('Amghrby\Workflows\Tasks\Task');
    }

    public function triggers(): HasMany
    {
        return $this->hasMany(Trigger::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany('Amghrby\Workflows\Loggers\WorkflowLog');
    }

    public function getTriggerByClass($class): Model|HasMany|null
    {
        return $this->triggers()->where('type', $class)->first();
    }
}
