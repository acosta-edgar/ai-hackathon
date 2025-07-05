<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_boards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('type')->default('general'); // e.g., 'general', 'tech', 'design', etc.
            $table->text('description')->nullable();
            $table->boolean('requires_authentication')->default(false);
            $table->json('authentication_details')->nullable();
            $table->json('search_parameters')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('search_frequency_hours')->default(24);
            $table->timestamp('last_searched_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_boards');
    }
};
