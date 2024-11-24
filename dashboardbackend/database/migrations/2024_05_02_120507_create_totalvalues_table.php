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
        Schema::create('totalvalues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('TotalFCInvoice');
            $table->unsignedBigInteger('TotalActualInvoice');
            $table->unsignedBigInteger('TotalActualCashin');
            $table->unsignedBigInteger('TotalActualCashout');
            $table->year('yearSelected');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('totalvalues');
    }
};
