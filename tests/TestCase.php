<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use UniqueWorkbench\SharedUi\SharedUiServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [SharedUiServiceProvider::class];
    }

    /** A client app built on the workbench: SSO configured and a config/workbench.php manifest */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('sso', [
            'base_url' => 'https://account.test',
            'client_id' => 'client-tt',
            'client_secret' => 'secret',
            'scopes' => [],
        ]);
        $app['config']->set('shared-ui.feature_api_key', 'feature-key');
        $app['config']->set('workbench', [
            'audiences' => ['employee', 'contractor'],
            'permissions' => [],
            'provides' => [
                'service-codes' => [
                    'label' => 'Service codes',
                    'description' => 'What the work was',
                    'endpoint' => '/api/features/service-codes',
                ],
            ],
            'uses' => [
                'schedules.shifts' => ['reason' => 'Shifts on the timesheet'],
            ],
        ]);
    }

    /** A throwaway RSA key pair: [private PEM, public PEM] */
    protected static function keyPair(): array
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $private);

        return [$private, openssl_pkey_get_details($key)['key']];
    }
}
