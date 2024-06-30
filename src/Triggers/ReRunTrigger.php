<?php

namespace Amghrby\Workflows\Triggers;

use Amghrby\Workflows\Loggers\WorkflowLog;

class ReRunTrigger
{
    public static function startWorkflow(WorkflowLog $log): void
    {
        $log->triggerable->start($log->elementable);
    }
}
