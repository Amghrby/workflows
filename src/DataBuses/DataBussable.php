<?php

namespace Amghrby\Workflows\DataBuses;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Amghrby\Workflows\Workflow;

trait DataBussable
{
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    public function getParentDataBusKeys($passedFields = [])
    {
        $newFields = $passedFields;

        if (! empty($this->parentable)) {
            foreach ($this->parentable::$output as $key => $value) {
                $newFields[$this->parentable->name.' - '.$key.' - '.$this->parentable->getFieldValue($value)] = $this->parentable->getFieldValue($value);
            }

            $newFields = $this->parentable->getParentDataBusKeys($newFields);
        }

        return $newFields;
    }

    public function getData(string $value, string $default = '')
    {
        return $this->dataBus->get($value, $default);
    }

    public function setDataArray(string $key, $value)
    {
        return $this->dataBus->setOutputArray($key, $value);
    }

    public function setData(string $key, $value)
    {
        return $this->dataBus->setOutput($key, $value);
    }
}
