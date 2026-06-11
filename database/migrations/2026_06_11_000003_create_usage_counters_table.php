<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usage_counters', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('metric');        // tickets_created | chat_sessions_started | remote_session_minutes | agents_registered | kb_lookups
            $table->string('period');        // YYYY-MM
            $table->unsignedInteger('count')->default(0);
            $table->timestamp('last_reset_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'metric', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usage_counters');
    }
};
