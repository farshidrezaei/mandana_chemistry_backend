<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_test', function (Blueprint $table) {
            $table->unsignedInteger('passed_duration')->default(0)->after('renewals_duration');
        });
    }

    public function down(): void
    {
        Schema::table('project_test', function (Blueprint $table) {
            $table->dropColumn(['passed_duration']);
        });
    }
};
