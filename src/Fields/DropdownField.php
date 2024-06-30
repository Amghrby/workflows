<?php

namespace Amghrby\Workflows\Fields;

use Amghrby\Workflows\Fields\FieldInterface;

class DropdownField implements FieldInterface
{
    public array $options;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public static function make(array $options): DropdownField
    {
        return new self($options);
    }

    public function get($element, $value, $field): array
    {
        return [
            'field' => $field,
            'value' => $value,
            'options' => $this->options,
        ];
    }
}
