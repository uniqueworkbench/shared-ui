<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use UniqueWorkbench\SharedUi\Workbench\Connections;

class ConnectionsTest extends TestCase
{
    private const TOKEN_URL = 'https://account.test/api/connections/token';

    private const PROVIDER_URL = 'https://timetracker.test/api/features/service-codes';

    private function connections(): Connections
    {
        return app(Connections::class);
    }

    private function codes(): array
    {
        return ['data' => [['id' => 1, 'code' => '100', 'name' => 'Cleaning', 'is_active' => true]]];
    }

    private function tokenAnswer(string $token = 'conn-1'): array
    {
        return ['token' => $token, 'expires_in' => 600, 'url' => self::PROVIDER_URL];
    }

    public function test_it_gets_a_token_then_calls_the_provider_directly(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response($this->tokenAnswer()),
            'timetracker.test/*' => Http::response($this->codes()),
        ]);

        $this->assertSame($this->codes(), $this->connections()->get('timetracker', 'service-codes', [], 7));

        Http::assertSent(fn (Request $r) => $r->url() === self::TOKEN_URL
            && $r->hasHeader('Authorization', 'Bearer cc')
            && $r['provider'] === 'timetracker' && $r['share'] === 'service-codes' && $r['organization_id'] === 7);
        Http::assertSent(fn (Request $r) => $r->url() === self::PROVIDER_URL . '?organization_id=7'
            && $r->hasHeader('Authorization', 'Bearer conn-1'));
    }

    public function test_answers_and_tokens_are_cached(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response($this->tokenAnswer()),
            'timetracker.test/*' => Http::response($this->codes()),
        ]);

        $this->connections()->get('timetracker', 'service-codes', [], 7);
        $this->connections()->get('timetracker', 'service-codes', [], 7);
        // Another query asks the provider again, with the same connection token
        $this->connections()->get('timetracker', 'service-codes', ['ids' => [1, 2]], 7);

        Http::assertSentCount(4); // client token, connection token, two provider calls
        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), self::PROVIDER_URL) && ($r->data()['ids'] ?? null) === '1,2');
    }

    public function test_the_last_good_answer_is_returned_when_the_provider_fails(): void
    {
        config(['workbench.connections_cache_seconds' => 0]);
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response($this->tokenAnswer()),
            'timetracker.test/*' => Http::sequence()->push($this->codes())->push(['message' => 'down'], 500),
        ]);

        $this->assertSame($this->codes(), $this->connections()->get('timetracker', 'service-codes', [], 7));
        $this->assertSame($this->codes(), $this->connections()->get('timetracker', 'service-codes', [], 7));
    }

    public function test_nothing_to_fall_back_on_answers_null(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response(['message' => 'down'], 503),
        ]);

        $this->assertNull($this->connections()->get('timetracker', 'service-codes', [], 7));
    }

    public function test_a_refused_client_token_is_dropped_and_asked_for_again(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::sequence()
                ->push(['access_token' => 'old', 'expires_in' => 3600])
                ->push(['access_token' => 'new', 'expires_in' => 3600]),
            self::TOKEN_URL => function (Request $r) {
                return $r->hasHeader('Authorization', 'Bearer old')
                    ? Http::response(['message' => 'Unauthenticated.'], 401)
                    : Http::response($this->tokenAnswer());
            },
            'timetracker.test/*' => Http::response($this->codes()),
        ]);

        $this->assertSame($this->codes(), $this->connections()->get('timetracker', 'service-codes', [], 7));
    }

    public function test_a_connection_token_the_provider_refuses_is_replaced_once(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::sequence()->push($this->tokenAnswer('conn-1'))->push($this->tokenAnswer('conn-2')),
            'timetracker.test/*' => function (Request $r) {
                return $r->hasHeader('Authorization', 'Bearer conn-1')
                    ? Http::response(['message' => 'Invalid connection token.'], 401)
                    : Http::response($this->codes());
            },
        ]);

        $this->assertSame($this->codes(), $this->connections()->get('timetracker', 'service-codes', [], 7));
        Http::assertSent(fn (Request $r) => $r->hasHeader('Authorization', 'Bearer conn-2'));
    }

    public function test_not_approved_answers_null_and_is_remembered_for_a_minute(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response(['message' => 'Not approved.'], 403),
        ]);

        $this->assertNull($this->connections()->get('timetracker', 'service-codes', [], 7));
        $this->assertNull($this->connections()->get('timetracker', 'service-codes', [], 7));
        $this->assertFalse($this->connections()->available('timetracker', 'service-codes', 7));

        Http::assertSentCount(2); // client token, one refusal
    }

    public function test_available_when_a_token_is_issued(): void
    {
        Http::fake([
            'account.test/oauth/token' => Http::response(['access_token' => 'cc', 'expires_in' => 3600]),
            self::TOKEN_URL => Http::response($this->tokenAnswer()),
        ]);

        $this->assertTrue($this->connections()->available('timetracker', 'service-codes', 7));
    }

    public function test_without_sso_nothing_is_called(): void
    {
        config(['sso.client_id' => null]);
        Http::fake();

        $this->assertNull($this->connections()->get('timetracker', 'service-codes', [], 7));
        $this->assertFalse($this->connections()->available('timetracker', 'service-codes', 7));
        Http::assertNothingSent();
    }
}
