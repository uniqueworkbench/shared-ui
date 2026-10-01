<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Http\Response;

/**
 * POST /api/features/directory-changed (feature-api): the account app says an
 * organization's shared directory changed, so the cached copies are dropped
 * and the next read is fresh.
 */
class DirectoryChangedController
{
    public function __invoke(Directory $directory): Response
    {
        $directory->flush();

        return response()->noContent();
    }
}
