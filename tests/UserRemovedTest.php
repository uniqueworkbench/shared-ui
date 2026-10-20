<?php

namespace UniqueWorkbench\SharedUi\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UserRemovedTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('auth.providers.users.model', RemovedTestUser::class);
        $app['config']->set('session.driver', 'database');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function ($table) {
            $table->id();
            $table->string('sso_id')->nullable();
            $table->string('remember_token')->nullable();
            $table->text('sso_access_token')->nullable();
            $table->text('sso_refresh_token')->nullable();
            $table->timestamps();
        });
        Schema::create('sessions', function ($table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable();
            $table->longText('payload');
            $table->integer('last_activity');
        });
    }

    public function test_the_removed_login_is_signed_out_and_others_are_left_alone(): void
    {
        $gone = RemovedTestUser::create(['sso_id' => '42', 'remember_token' => 'r', 'sso_access_token' => 'a', 'sso_refresh_token' => 'f']);
        $other = RemovedTestUser::create(['sso_id' => '7', 'remember_token' => 'r2']);
        DB::table('sessions')->insert([
            ['id' => 's1', 'user_id' => $gone->id, 'payload' => '', 'last_activity' => 0],
            ['id' => 's2', 'user_id' => $other->id, 'payload' => '', 'last_activity' => 0],
        ]);

        $this->postJson('/api/features/user-removed', ['user_id' => 42], ['X-Api-Key' => 'feature-key'])->assertNoContent();

        $gone->refresh();
        $this->assertNull($gone->remember_token);
        $this->assertNull($gone->sso_access_token);
        $this->assertNull($gone->sso_refresh_token);
        $this->assertSame(['s2'], DB::table('sessions')->pluck('id')->all());
        $this->assertSame('r2', $other->refresh()->remember_token);
    }

    public function test_it_needs_the_feature_api_key_and_a_user_id(): void
    {
        $this->postJson('/api/features/user-removed', ['user_id' => 42], ['X-Api-Key' => 'wrong'])->assertUnauthorized();
        $this->postJson('/api/features/user-removed', [], ['X-Api-Key' => 'feature-key'])->assertUnprocessable();
    }
}

class RemovedTestUser extends Authenticatable
{
    protected $table = 'users';

    protected $guarded = [];
}
