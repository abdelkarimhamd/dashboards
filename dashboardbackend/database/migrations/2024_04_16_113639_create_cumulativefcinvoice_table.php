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
        Schema::create('cumulativefcinvoice', function (Blueprint $table) {
            $table->id();
            $table->decimal('M01', 20, 2)->nullable();
            $table->decimal('M02', 20, 2)->nullable();
            $table->decimal('M03', 20, 2)->nullable();
            $table->decimal('M04', 20, 2)->nullable();
            $table->decimal('M05', 20, 2)->nullable();
            $table->decimal('M06', 20, 2)->nullable();
            $table->decimal('M07', 20, 2)->nullable();
            $table->decimal('M08', 20, 2)->nullable();
            $table->decimal('M09', 20, 2)->nullable();
            $table->decimal('M10', 20, 2)->nullable();
            $table->decimal('M11', 20, 2)->nullable();
            $table->decimal('M12', 20, 2)->nullable();
            $table->integer('yearSelected')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cumulativefcinvoice');
    }
};
