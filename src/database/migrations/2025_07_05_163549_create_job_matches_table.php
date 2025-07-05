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
        Schema::create('job_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_profile_id')->constrained('user_profiles')->onDelete('cascade');
            $table->foreignId('job_id')->constrained()->onDelete('cascade');
            $table->foreignId('search_criteria_id')->nullable()->constrained()->onDelete('set null');
            
            // Match scores (0-100)
            $table->integer('overall_score')->default(0);
            $table->integer('skills_score')->default(0);
            $table->integer('experience_score')->default(0);
            $table->integer('education_score')->default(0);
            $table->integer('company_fit_score')->default(0);
            
            // Match details
            $table->json('strengths')->nullable(); // Array of strengths in the match
            $table->json('weaknesses')->nullable(); // Array of weaknesses in the match
            $table->json('missing_skills')->nullable(); // Skills from the job that are missing from the profile
            $table->json('matching_skills')->nullable(); // Skills that match between the job and profile
            
            // AI Analysis
            $table->text('match_summary')->nullable(); // AI-generated summary of the match
            $table->text('improvement_suggestions')->nullable(); // AI-generated suggestions for improving the match
            $table->text('application_advice')->nullable(); // AI-generated advice for the application
            
            // User actions
            $table->boolean('is_interested')->nullable(); // User has shown interest
            $table->boolean('is_not_interested')->default(false); // User is not interested
            $table->text('user_notes')->nullable(); // User's personal notes about this match
            $table->timestamp('viewed_at')->nullable(); // When the user viewed this match
            $table->timestamp('applied_at')->nullable(); // When the user applied for this job
            $table->timestamp('rejected_at')->nullable(); // When the user was rejected
            
            // Status tracking
            $table->string('status')->default('new'); // new, viewed, applied, interview, offer, rejected, closed
            $table->json('status_history')->nullable(); // History of status changes with timestamps
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for better query performance
            $table->index(['user_profile_id', 'overall_score']);
            $table->index(['job_id', 'overall_score']);
            $table->index('status');
            $table->index('is_interested');
            $table->index('is_not_interested');
            $table->index('applied_at');
            
            // Ensure one match per user-profile-job combination
            $table->unique(['user_profile_id', 'job_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_matches');
    }
};
