<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class VerifyConnectionTokenTest extends TestCase
{
    private static array $keys;

    private static array $otherKeys;

    /** The public key the account app serves now */
    private string $served;

    protected function setUp(): void
    {
        parent::setUp();

        self::$keys ??= self::keyPair();
        self::$otherKeys ??= self::keyPair();

        Route::middleware('workbench.share:service-codes')->get('/api/features/service-codes', fn () => [
            'connection' => request()->attributes->get('workbench.connection'),
        ]);
        $this->served = self::$keys[1];
        Http::fake(['account.test/api/connections/public-key' => fn () => Http::response(['alg' => 'RS256', 'key' => $this->served])]);
    }

    private function token(array $claims = [], ?string $privateKey = null): string
    {
        return JWT::encode($claims + [
            'iss' => 'https://account.test',
            'sub' => 'schedules',
            'aud' => 'client-tt',
            'share' => 'service-codes',
            'org' => 7,
            'iat' => time(),
            'exp' => time() + 600,
            'jti' => (string) Str::uuid(),
        ], $privateKey ?? self::$keys[0], 'RS256');
    }

    private function share(string $token, int $organizationId = 7)
    {
        return $this->withToken($token)->getJson('/api/features/service-codes?organization_id=' . $organizationId);
    }

    public function test_a_valid_token_is_accepted_and_the_caller_recorded(): void
    {
        $this->share($this->token(['jti' => 'abc']))
            ->assertOk()
            ->assertJsonPath('connection', ['app' => 'schedules', 'share' => 'service-codes', 'organization_id' => 7, 'jti' => 'abc']);
    }

    public function test_the_public_key_is_cached(): void
    {
        $this->share($this->token())->assertOk();
        $this->share($this->token())->assertOk();

        Http::assertSentCount(1);
    }

    public function test_tokens_for_something_else_are_refused(): void
    {
        $this->share($this->token(['aud' => 'client-other']))->assertUnauthorized();
        $this->share($this->token(['share' => 'time-entries']))->assertUnauthorized();
        $this->share($this->token(['org' => 8]))->assertUnauthorized();
        $this->share($this->token(['iss' => 'https://evil.test']))->assertUnauthorized();
        $this->withToken($this->token())->getJson('/api/features/service-codes')->assertUnauthorized();
    }

    public function test_expired_tokens_are_refused_after_the_leeway(): void
    {
        $this->share($this->token(['exp' => time() - 10]))->assertOk();
        $this->share($this->token(['exp' => time() - 120]))->assertUnauthorized();
    }

    public function test_a_bad_signature_is_refused_after_fetching_the_key_again(): void
    {
        $this->share($this->token([], self::$otherKeys[0]))->assertUnauthorized()->assertJson(['message' => 'Invalid connection token.']);

        Http::assertSentCount(2);
    }

    public function test_a_rotated_key_is_picked_up(): void
    {
        $this->share($this->token())->assertOk();

        // The account app's key changes
        $this->served = self::$otherKeys[1];
        $this->share($this->token([], self::$otherKeys[0]))->assertOk();
    }

    public function test_the_feature_api_key_is_still_accepted(): void
    {
        $this->getJson('/api/features/service-codes?organization_id=7', ['X-Api-Key' => 'feature-key'])->assertOk();
        $this->getJson('/api/features/service-codes?organization_id=7', ['X-Api-Key' => 'wrong'])->assertUnauthorized();
        $this->getJson('/api/features/service-codes?organization_id=7')->assertUnauthorized();
        $this->share('not-a-jwt')->assertUnauthorized();
    }
}
