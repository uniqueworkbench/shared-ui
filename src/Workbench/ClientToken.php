<?php

namespace UniqueWorkbench\SharedUi\Workbench;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * A client-credentials token for this app's SSO client (config/sso.php), for the account app's
 * server-to-server APIs — the directory (Directory) and the mail relay (WorkbenchMailTransport).
 * Cached per scope until shortly before it expires; forget() drops one the account app refused.
 */
class ClientToken
{
    public static function get(string $scope): string
    {
        if ($token = Cache::get(self::key($scope))) {
            return $token;
        }

        $response = Http::asForm()->acceptJson()->timeout(10)->post(config('sso.base_url') . '/oauth/token', array_filter([
            'grant_type' => 'client_credentials',
            'client_id' => config('sso.client_id'),
            'client_secret' => config('sso.client_secret'),
            // A client limited to certain scopes must ask for this one; an unrestricted client needs none
            'scope' => config('sso.scopes') ? $scope : null,
        ]))->throw()->json();

        Cache::put(self::key($scope), $response['access_token'], max(60, (int) ($response['expires_in'] ?? 3600) - 60));

        return $response['access_token'];
    }

    public static function forget(string $scope): void
    {
        Cache::forget(self::key($scope));
    }

    private static function key(string $scope): string
    {
        return 'workbench.client-token.' . $scope;
    }
}
