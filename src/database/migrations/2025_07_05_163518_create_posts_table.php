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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->unique()->comment('External ID from the job board');
            $table->foreignId('job_board_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('company_name');
            $table->string('company_website')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->string('job_type')->nullable(); // full-time, part-time, contract, etc.
            $table->string('experience_level')->nullable(); // entry, mid, senior, executive
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');
            $table->string('salary_period')->nullable(); // year, month, hour, etc.
            $table->json('skills')->nullable();
            $table->json('categories')->nullable();
            $table->string('apply_url');
            $table->string('job_url');
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('raw_data')->nullable(); // Raw data from the job board
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index(['job_board_id', 'is_active']);
            $table->index('title');
            $table->index('company_name');
            $table->index('is_remote');
            $table->index('job_type');
            $table->index('experience_level');
            $table->index('posted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
