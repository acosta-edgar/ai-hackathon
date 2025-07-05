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
        Schema::create('search_criteria', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->json('keywords')->nullable();
            $table->json('locations')->nullable();
            $table->string('job_type')->nullable(); // full-time, part-time, contract, etc.
            $table->string('experience_level')->nullable(); // entry, mid, senior, executive
            $table->decimal('min_salary', 12, 2)->nullable();
            $table->decimal('max_salary', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->boolean('is_remote')->nullable();
            $table->json('industries')->nullable();
            $table->json('companies')->nullable();
            $table->json('job_titles')->nullable();
            $table->json('skills_included')->nullable();
            $table->json('skills_excluded')->nullable();
            $table->integer('days_posted')->nullable(); // Max days since job was posted
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_criteria');
    }
};
