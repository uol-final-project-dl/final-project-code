<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prototypes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->uuid()->unique();
            $table->string('description');
            $table->string('status')->default('queued');
            $table->string('bundle')->nullable();
            $table->longText('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prototypes');
    }
};
