<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_admin')->default(false);
            $table->unsignedBigInteger('person_id')->nullable();
            $table->json('access_ids')->nullable(); // آرایه‌ای از IDهای جدول people
            $table->timestamps(); // فقط یک بار created_at و updated_at را اضافه می‌کند
            $table->foreign('person_id')->references('id')->on('people')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};