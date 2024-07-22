<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->string('attachment')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {

        });
    }
};
