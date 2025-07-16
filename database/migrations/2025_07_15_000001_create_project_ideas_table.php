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
        Schema::create('project_ideas', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->text('title');
            $table->longText('description');
            $table->unsignedTinyInteger('ranking')->default(0);
            $table->string('status')->default(StatusEnum::REQUEST_DATA->value);
            $table->timestamps();
        });

        Schema::table('project_ideas', static function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_ideas');
    }
};
