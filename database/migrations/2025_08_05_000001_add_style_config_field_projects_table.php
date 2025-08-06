<?php

/** @noinspection SpellCheckingInspection */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->text('style_config')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', static function (Blueprint $table) {
            $table->dropColumn('style_config');
        });
    }
};
