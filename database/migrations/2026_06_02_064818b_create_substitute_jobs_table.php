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
        Schema::create('substitute_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_profile_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->string('grade_level');
            $table->time('start_time');
            $table->time('end_time');
            $table->date('date');
            $table->string('status')->default('open'); // open, filled, completed, cancelled
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('substitute_jobs');
    }
};
