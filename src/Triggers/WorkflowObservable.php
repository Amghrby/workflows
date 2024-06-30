<?php

namespace Amghrby\Workflows\Triggers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

trait WorkflowObservable
{

    /**
     * Boot the WorkflowObservable trait.
     *
     * This method registers event listeners for various Eloquent model events,
     * including standard CRUD operations and soft delete events if SoftDeletes trait is used.
     */
    public static function bootWorkflowObservable(): void
    {
        $events = [
            'retrieved', 'creating', 'created', 'updating', 'updated',
            'saving', 'saved', 'deleting', 'deleted'
        ];

        foreach ($events as $event) {
            static::$event(static function (Model $model) use ($event) {
                self::startWorkflows($model, $event);
            });
        }

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            static::restoring(static function (Model $model) {
                self::startWorkflows($model, 'restoring');
            });

            static::restored(static function (Model $model) {
                self::startWorkflows($model, 'restored');
            });

            static::restored(static function (Model $model) {
                self::startWorkflows($model, 'forceDeleted');
            });
        }
    }

    /**
     * Retrieve registered triggers for a specific class and event.
     *
     * @param string $class The fully qualified class name of the model.
     * @param string $event The event name to retrieve triggers for.
     * @return Collection Collection of triggers.
     */
    public static function getRegisteredTriggers(string $class, string $event): Collection
    {
        $class_array = explode('\\', $class);

        $className = $class_array[count($class_array) - 1];

        return Trigger::where('type', ObserverTrigger::class)
            ->where('data_fields->class->value', 'like', '%'.$className.'%')
            ->where('data_fields->event->value', $event)
            ->get();
    }

    /**
     * Start workflows for a given model and event.
     *
     * @param Model $model The Eloquent model instance.
     * @param string $event The event name triggering the workflows.
     * @return void
     */
    public static function startWorkflows(Model $model, string $event): void
    {
        if (!in_array($event, config('workflows.triggers.Observers.events'), true)) {
            return;
        }

        foreach (self::getRegisteredTriggers(get_class($model), $event) as $trigger) {
            $trigger->start($model);
        }
    }
}
