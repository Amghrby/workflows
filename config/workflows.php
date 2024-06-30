<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Resources
    |--------------------------------------------------------------------------
    |
    | Here you can register all the Data Resources which should be used in the Workflow Package. You can also
    | deactivate Data Resources just by deleting them here.
    |
    */
    'data_resources' => [
        'ModelResource' => Amghrby\Workflows\DataBuses\ModelResource::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Triggers
    |--------------------------------------------------------------------------
    |
    | Here you can register all the Triggers which should be used in the Workflow Package. You can also
    | deactivate Triggers just by deleting them here.
    |
    | Observers
    |
    | Events:
    | You can register all the events the Trigger should listen to here.
    |
    | Classes:
    | You can register the Classes which can be used for the ObserverTrigger.
    |
    */
    'triggers' => [

        'types' => [
            'ObserverTrigger' => Amghrby\Workflows\Triggers\ObserverTrigger::class,
        ],

        'Observers' => [
            'events' => [
                'retrieved',
                'creating',
                'created',
                'updating',
                'updated',
                'saving',
                'saved',
                'deleting',
                'deleted',
                'restoring',
                'restored',
                'forceDeleted',
            ],
            'classes' => [
                \App\Models\User::class => 'User',
                \Amghrby\Workflows\Loggers\WorkflowLog::class => 'WorkflowLog',
            ],
        ],
    ],

    'queue' => 'redis',

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Configure if the package should load its default routes. Default its not using the default routes. We recommend
    | using them as described in the Documentation because you should put a Auth middleware on them.
    */
    'prefix' => 'workflows',

    /*
    |--------------------------------------------------------------------------
    | Database prefixing
    |--------------------------------------------------------------------------
    |
    | We know how annoying it can be if a package brings a table name into your system which you are even worse another
    | package all ready uses. With the db_prefix you can set a prefix to the tables to avoid this conflict.
    | This changes needs to be done before the Migrations are running.
    */
        'db_prefix' => '',
];
