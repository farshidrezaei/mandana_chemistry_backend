<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_test', function (Blueprint $table) {
            $table->boolean('has_been_notified')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('project_test', function (Blueprint $table) {
            $table->dropColumn(['has_been_notified']);
        });
    }
};
