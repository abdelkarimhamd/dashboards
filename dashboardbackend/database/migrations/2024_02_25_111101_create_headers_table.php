<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('headers', function (Blueprint $table) {
            $table->id();
            $table->string('filePath');
            $table->string('ProjectImageFilePath');
            $table->string('projectType');
            $table->string('projectName');
            $table->string('clientName');
            $table->string('projectLocation');
            $table->integer('projectDuration');
            $table->date('projectDate');
            $table->decimal('projectValue', 10, 2);
            $table->integer('ProjetPeakManpower');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('headers');
    }
};