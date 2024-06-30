<?php

namespace Amghrby\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ModelResource implements Resource
{
    public function getData(string $name, string $value, Model $model, DataBus $dataBus)
    {
        return $model->{$value};
    }

    public static function getValues(Model $element, $value, $field_name): array
    {
        $classes = [];
        foreach ($element->workflow->triggers as $trigger) {
            if (isset($trigger->data_fields['class']['value'])) {
                $classes[] = $trigger->data_fields['class']['value'];
            }
        }

        $variables = [];
        foreach ($classes as $class) {
            $model = new $class;
            foreach (Schema::getColumnListing($model->getTable()) as $item) {
                $variables[$class.'->'.$item] = $item;
            }
        }

        return $variables;
    }

    public static function checkCondition(Model $element, DataBus $dataBus, string $field, string $operator, string $value): bool
    {
        return match ($operator) {
            'equal' => $element->{$field} == $value,
            'not_equal' => $element->{$field} != $value,
            default => true,
        };
    }

    public static function loadResourceIntelligence(Model $element, $value, $field_name): array
    {
        $variables = self::getValues($element, $value, $field_name);

        return [
            'fields' => $variables,
            'value' => $value,
            'field' => $field_name,
        ];
    }
}
