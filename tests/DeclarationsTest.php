<?php

namespace UniqueWorkbench\SharedUi\Tests;

use UniqueWorkbench\SharedUi\Workbench\Workbench;

class DeclarationsTest extends TestCase
{
    public function test_shares_and_uses_are_normalised(): void
    {
        config([
            'workbench.provides' => [
                'service-codes' => ['label' => 'Service codes', 'endpoint' => 'api/features/service-codes'],
                'Bad_Key' => ['endpoint' => '/api/features/x'],
                'outside' => ['endpoint' => '/api/other'],
                'plain' => 'Just a label',
            ],
            'workbench.uses' => [
                'timetracker.service-codes' => ['reason' => 'Pickers'],
                'schedules.shifts' => 'Shifts',
                'no-share' => ['reason' => 'x'],
                'timetracker.Bad' => [],
            ],
        ]);

        $this->assertSame([[
            'key' => 'service-codes',
            'label' => 'Service codes',
            'description' => null,
            'endpoint' => '/api/features/service-codes',
        ]], Workbench::declaredShares());

        $this->assertSame([
            ['provider' => 'timetracker', 'share' => 'service-codes', 'reason' => 'Pickers'],
            ['provider' => 'schedules', 'share' => 'shifts', 'reason' => 'Shifts'],
        ], Workbench::declaredUses());
    }

    public function test_the_permissions_endpoint_includes_provides_and_uses(): void
    {
        $this->getJson('/api/features/permissions', ['X-Api-Key' => 'feature-key'])
            ->assertOk()
            ->assertJsonPath('permissions', [])
            ->assertJsonPath('contact_types', [])
            ->assertJsonPath('provides.0.key', 'service-codes')
            ->assertJsonPath('provides.0.endpoint', '/api/features/service-codes')
            ->assertJsonPath('uses.0', ['provider' => 'schedules', 'share' => 'shifts', 'reason' => 'Shifts on the timesheet']);

        $this->getJson('/api/features/permissions', ['X-Api-Key' => 'wrong'])->assertUnauthorized();
    }
}
