<?php

namespace Amghrby\Workflows\Tasks;

use Amghrby\Workflows\Tasks\Task;

class CreateModel extends Task
{

    public static array $fields = [
        'Model' => 'model',
        'Data' => 'data',
    ];

    public static array $output = [
        'Output' => 'output',
    ];

    public function execute(): void
    {
        $model = $this->getData('model');
        $data = $this->getData('data');

        foreach ($data as $field => $value) {
            $model->{$field} = $value;
        }

        $model->save();

        $this->setData('output', $model);
    }
}
