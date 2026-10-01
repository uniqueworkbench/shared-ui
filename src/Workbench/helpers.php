<?php

use UniqueWorkbench\SharedUi\Workbench\Workbench;

if (! function_exists('workbench')) {
    /** The signed-in user's organization context (UniqueWorkbench\SharedUi\Workbench\Workbench) */
    function workbench(): Workbench
    {
        return app(Workbench::class);
    }
}
