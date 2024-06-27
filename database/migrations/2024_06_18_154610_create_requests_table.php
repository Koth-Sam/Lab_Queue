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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('student_id');
            $table->string('course_name');
            $table->string('course_code');
            $table->enum('request_type', ['assistance', 'sign-off']);
            $table->text('description')->nullable();
            $table->string('subject_area');
            $table->string('seat_number');
            $table->string('screenshot')->nullable();
            $table->string('code_url')->nullable();
            $table->enum('status', ['pending', 'accepted', 'completed'])->default('pending');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
