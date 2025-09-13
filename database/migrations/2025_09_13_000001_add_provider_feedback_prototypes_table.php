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
        Schema::table('prototypes', static function (Blueprint $table) {
            $table->string('provider')->nullable()->after('id');
            $table->integer('feedback_score')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prototypes', static function (Blueprint $table) {
            $table->dropColumn('provider');
            $table->dropColumn('feedback_score');
        });
    }
};
