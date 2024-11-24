<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_details', function (Blueprint $table) {
            $table->bigIncrements('id');  // Primary key for project_details table
            $table->unsignedBigInteger('ProjectID');  // Foreign key to reference the headers table
            $table->string('branch');
            $table->string('ProjectName');
            $table->integer('YearSelected');
            $table->string('MainScope');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('ProjectID')->references('id')->on('headers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_details');
    }
};

