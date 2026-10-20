<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * POST /api/features/user-removed (feature-api): the account app says a login is gone
 * ({user_id} = its user id), so that person is signed out here at once (SignOutRemovedUser).
 */
class UserRemovedController
{
    public function __invoke(Request $request, SignOutRemovedUser $signOut): Response
    {
        $validated = $request->validate(['user_id' => ['required', 'integer']]);

        $signOut((string) $validated['user_id']);

        return response()->noContent();
    }
}
