<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('keyissuesnotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projectId')->constrained('headers');
            $table->text('note');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyissuesnotes');
    }
};
