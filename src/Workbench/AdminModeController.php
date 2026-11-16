<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Turn Admin Mode on or off in this app, from the user menu (like the account app's). Only a Super Admin (the account app's
 * is_admin) can; everyone else gets a 403. It is the session's, so each sign-in starts with it off and each app keeps its own.
 */
class AdminModeController
{
    public function update(Request $request): RedirectResponse
    {
        $workbench = workbench();
        abort_unless($workbench->isAdminEligible(), 403);

        $workbench->setAdminMode($request->boolean('on'));

        return back();
    }
}
