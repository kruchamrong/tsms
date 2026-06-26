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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_code')->unique();
            $table->string('khmer_name');
            $table->string('english_name');
            $table->enum('gender', ['M', 'F']);
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('telegram_id')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('qualification')->nullable();
            $table->enum('employment_type', ['Full-Time', 'Part-Time', 'Visiting']);
            $table->enum('status', ['Active', 'Inactive', 'On-Leave'])->default('Active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
