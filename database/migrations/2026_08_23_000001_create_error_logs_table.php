<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('app');
            $table->string('environment');
            $table->string('exception_class');
            $table->text('message')->nullable();
            $table->string('file')->nullable();
            $table->unsignedInteger('line')->nullable();
            $table->longText('trace')->nullable();
            $table->string('url')->nullable();
            $table->string('method', 10)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->index(['app', 'created_at']);
            $table->index('exception_class');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
