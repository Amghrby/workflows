<?php

namespace Amghrby\Workflows\Fields;

interface FieldInterface
{
    public function render($element, $value, $field);
}
