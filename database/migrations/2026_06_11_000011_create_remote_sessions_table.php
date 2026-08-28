<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('remote_sessions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('tenant_id');
            $table->ulid('ticket_id');
            // users.id is auto-increment bigint (standard Laravel default, kept
            // deliberately -- SSO users are looked up by email, not external
            // ID, per docs/DATABASE_CONVENTIONS.md), NOT a ulid like every
            // other table's PK here. Fixed 2026-08-28: this migration never
            // successfully ran before (FK type mismatch), found while setting
            // up this environment for unrelated SSO work.
            $table->unsignedBigInteger('engineer_id');
            $table->unsignedBigInteger('customer_id');
            $table->text('session_token')->nullable();
            $table->enum('status', ['requested', 'accepted', 'active', 'ended', 'declined'])
                ->default('requested');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration_minutes')->default(0);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            $table->foreign('ticket_id')->references('id')->on('tickets')->cascadeOnDelete();
            $table->foreign('engineer_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('users')->cascadeOnDelete();

            $table->index(['tenant_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['ticket_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('remote_sessions');
    }
};
