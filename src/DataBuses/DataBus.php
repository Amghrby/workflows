<?php

namespace Amghrby\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Model;

class DataBus
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collectData(Model $model, $fields): void
    {
        foreach ($this->filterFields($fields) as $name => $field) {
            if ($this->shouldSkipField($name, $field)) {
                continue;
            }

            $resource = $this->getResource($field);
            $this->data[$name] = $resource->getData($name, $field['value'], $model, $this);
        }
    }

    private function shouldSkipField($name, $field): bool
    {
        return $name === 'file' && empty($field['value']);
    }

    private function getResource($field): ModelResource
    {
        return new $field['type'] ?? new ModelResource;
    }

    private function filterFields($fields): array
    {
        return array_filter($fields, static function($field, $name) {
            return $name !== 'description';
        }, ARRAY_FILTER_USE_BOTH);
    }

    public function toString(): string
    {
        $output = '';

        foreach ($this->data as $line) {
            $output .= $line.'\n';
        }

        return $output;
    }

    public function get(string $key, string $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function setOutput(string $key, $value): void
    {
        $this->data[$this->get($key, $key)] = $value;
    }

    public function setOutputArray(string $key, string $value): void
    {
        $this->data[$this->get($key, $key)][] = $value;
    }
}
