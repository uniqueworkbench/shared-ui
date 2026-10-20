<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;

/**
 * Signs out, everywhere, the local user whose account app login is gone (deleted, or removed
 * with Profile → Delete User): their sessions are deleted (database sessions — other drivers
 * can't be searched by user), their remember-me token cleared and the account app tokens kept
 * for them (sso_access_token, sso_refresh_token, sso_token_expires_at — whichever the table has)
 * dropped. Local users are found by `sso_id`, the account app's user id. The user row itself
 * stays, so the app's records keep pointing at it.
 */
class SignOutRemovedUser
{
    private const TOKEN_COLUMNS = ['remember_token', 'sso_access_token', 'sso_refresh_token', 'sso_token_expires_at'];

    /** How many local users were signed out */
    public function __invoke(string $ssoId): int
    {
        $model = config('auth.providers.users.model');
        $users = $model::where('sso_id', $ssoId)->get();

        foreach ($users as $user) {
            $user->forceFill(array_fill_keys(array_intersect(self::TOKEN_COLUMNS, array_keys($user->getAttributes())), null))->save();
            $this->deleteSessions($user);
        }

        return $users->count();
    }

    private function deleteSessions(Authenticatable $user): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
            ->where('user_id', $user->getAuthIdentifier())
            ->delete();
    }
}
