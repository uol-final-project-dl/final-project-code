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
        Schema::create('project_documents', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->text('filename');
            $table->string('type');
            $table->longText('content')->nullable();
            $table->string('error_message')->nullable();
            $table->string('status')->default(StatusEnum::QUEUED);
            $table->timestamps();
        });

        Schema::table('project_documents', static function (Blueprint $table) {
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documents');
    }
};
