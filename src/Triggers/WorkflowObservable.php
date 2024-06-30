<?php

namespace Amghrby\Workflows\Triggers;

use Illuminate\Database\Eloquent\Model;

trait WorkflowObservable
{
    public static function bootWorkflowObservable(): void
    {
        static::retrieved(static function (Model $model) {
            self::startWorkflows($model, 'retrieved');
        });
        static::creating(static function (Model $model) {
            self::startWorkflows($model, 'creating');
        });
        static::created(static function (Model $model) {
            self::startWorkflows($model, 'created');
        });
        static::updating(static function (Model $model) {
            self::startWorkflows($model, 'updating');
        });
        static::updated(static function (Model $model) {
            self::startWorkflows($model, 'updated');
        });
        static::saving(static function (Model $model) {
            self::startWorkflows($model, 'saving');
        });
        static::saved(static function (Model $model) {
            self::startWorkflows($model, 'saved');
        });
        static::deleting(static function (Model $model) {
            self::startWorkflows($model, 'deleting');
        });
        static::deleted(static function (Model $model) {
            self::startWorkflows($model, 'deleted');
        });
        //TODO: check why they are not available here
        /*static::restoring(function (Model $model) {
           self::startWorkflows($model, 'restoring');
        });
        static::restored(function (Model $model) {
           self::startWorkflows($model, 'restored');
        });
        static::forceDeleted(function (Model $model) {
            self::startWorkflows($model, 'forceDeleted');
        });*/
    }

    public static function getRegisteredTriggers(string $class, string $event)
    {
        $class_array = explode('\\', $class);

        $className = $class_array[count($class_array) - 1];

        return Trigger::where('type', 'Amghrby\Workflows\Triggers\ObserverTrigger')
            ->where('data_fields->class->value', 'like', '%'.$className.'%')
            ->where('data_fields->event->value', $event)
            ->get();
    }

    public static function startWorkflows(Model $model, string $event)
    {
        if (! in_array($event, config('workflows.triggers.Observers.events'))) {
            return;
        }

        foreach (self::getRegisteredTriggers(get_class($model), $event) as $trigger) {
            $trigger->start($model);
        }
    }
}
