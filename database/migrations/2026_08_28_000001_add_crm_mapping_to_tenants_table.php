<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Matches inteteam_sso's Company.id (same value CRM's Company.sso_company_id
            // stores) -- resolves which Tenant a tenant_admin's SSO claims belong to.
            $table->string('sso_company_id')->nullable()->unique()->after('slug');

            // CRM's Company.slug -- used directly as the {company} route param when
            // relaying customer OTP requests to inteteam_crm's public OTP API, avoiding
            // an extra lookup call on every request.
            $table->string('crm_company_slug')->nullable()->after('sso_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['sso_company_id', 'crm_company_slug']);
        });
    }
};
