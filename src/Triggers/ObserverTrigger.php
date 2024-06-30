<?php

namespace Amghrby\Workflows\Triggers;

use Amghrby\Workflows\Fields\DropdownField;

class ObserverTrigger extends Trigger
{

    public static array $fields = [
        'Class' => 'class',
        'Event' => 'event',
    ];

    public function inputFields(): array
    {
        return [
            'class' => DropdownField::make(config('workflows.triggers.Observers.classes')),
            'event' => DropdownField::make(array_combine(config('workflows.triggers.Observers.events'), config('workflows.triggers.Observers.events'))),
        ];
    }
}
