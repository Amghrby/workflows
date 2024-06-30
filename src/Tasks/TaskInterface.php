<?php

namespace Amghrby\Workflows\Tasks;

use Illuminate\Database\Eloquent\Model;
use Amghrby\Workflows\DataBuses\DataBus;

interface TaskInterface
{
    /**
     * Execute the Action return Value tells you about the success.
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Checks if all Conditions pass for this Action.
     *
     * @param  Model  $model
     * @param  DataBus  $data
     * @return bool
     */
    public function checkConditions(Model $model, DataBus $data): bool;
}
