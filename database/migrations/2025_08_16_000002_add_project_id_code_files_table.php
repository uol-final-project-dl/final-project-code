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
        Schema::table('code_files', static function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('code_files', static function (Blueprint $table) {
            $table->dropColumn('project_id');
        });
    }
};
