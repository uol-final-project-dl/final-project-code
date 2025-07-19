<?php

use App\Enums\StatusEnum;
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
            $table->unsignedBigInteger('project_idea_id');
            $table->uuid()->unique();
            $table->string('title');
            $table->longText('description');
            $table->string('status')->default(StatusEnum::QUEUED->value);
            $table->string('bundle')->nullable();
            $table->longText('log')->nullable();
            $table->timestamps();
        });

        Schema::table('prototypes', static function (Blueprint $table) {
            $table->foreign('project_idea_id')->references('id')->on('project_ideas')->onDelete('cascade');
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
