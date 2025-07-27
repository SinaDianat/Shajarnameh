<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('father_id')->nullable();
            $table->unsignedBigInteger('mother_id')->nullable();
            $table->json('children_ids')->nullable(); // آرایه‌ای از IDهای فرزندان
            $table->json('partners_ids')->nullable(); // آرایه‌ای از IDهای همسران
            $table->enum('gender', ['male', 'female']);
            $table->unsignedBigInteger('city_of_birth')->nullable();
            $table->date('birthday')->nullable();
            $table->unsignedBigInteger('city_of_die')->nullable();
            $table->date('date_of_die')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('city_of_life')->nullable();
            $table->unsignedBigInteger('occupation_id')->nullable();
            $table->timestamps();

            $table->foreign('father_id')->references('id')->on('people')->onDelete('set null');
            $table->foreign('mother_id')->references('id')->on('people')->onDelete('set null');
            $table->foreign('city_of_birth')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('city_of_die')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('city_of_life')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('occupation_id')->references('id')->on('occupations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};