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
        Schema::create('salary_monthly_base', function (Blueprint $table) {
          $table->id();
          $table->foreignId('projectId')->constrained('headers');
          $table->decimal('jan', 19, 4)->default(0);
          $table->decimal('feb', 19, 4)->default(0);
          $table->decimal('mar', 19, 4)->default(0);
          $table->decimal('apr', 19, 4)->default(0);
          $table->decimal('may', 19, 4)->default(0);
          $table->decimal('jun', 19, 4)->default(0);
          $table->decimal('jul', 19, 4)->default(0);
          $table->decimal('aug', 19, 4)->default(0);
          $table->decimal('sep', 19, 4)->default(0);
          $table->decimal('oct', 19, 4)->default(0);
          $table->decimal('nov', 19, 4)->default(0);
          $table->decimal('december', 19, 4)->default(0);
          $table->integer('year');
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_monthly_base');
    }
};
