<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_test', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->unsignedInteger('order');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->boolean('is_mismatched')->default(false);
            $table->unsignedInteger('renewals_count')->default(0);
            $table->unsignedInteger('renewals_duration')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_test');
    }
};
