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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('lastname');
            $table->string('email')->unique();
            $table->string('password');
            $table->dateTime('last_login')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->boolean('is_active')->default(true);
            $table->enum('role', ['manage', 'user'])->default('user');
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
           $table->id();
           $table->foreignId('user_id')->constrained('users');
           $table->string('ip_address',45)->nullable();
           $table->string('country',100)->nullable();
           $table->string('city',100)->nullable();
           $table->string('latitude',70)->nullable();
           $table->string('longitude',70)->nullable();
           $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
