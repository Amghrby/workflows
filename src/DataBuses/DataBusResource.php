<?php

namespace Amghrby\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class DataBusResource implements Resource
{
    public function getData(string $name, string $value, Model $model, DataBus $dataBus)
    {
        return $dataBus->data[$value];
    }

    public static function checkCondition(Model $element, DataBus $dataBus, string $field, string $operator, string $value): bool
    {
        switch ($operator) {
            case 'equal':
                return $dataBus->data[$dataBus->data[$field]] == $value;
            case 'not_equal':
                return $dataBus->data[$dataBus->data[$field]] != $value;
            default:
                return true;
        }
    }

    public static function getValues(Model $element, $value, $field)
    {
        return $element->getParentDataBusKeys();
    }

    public static function loadResourceIntelligence(Model $element, $value, $field): array
    {
        $fields = self::getValues($element, $value, $field);

        return [
            'fields' => $fields,
            'value' => $value,
            'field' => $field,
        ];
    }
}
