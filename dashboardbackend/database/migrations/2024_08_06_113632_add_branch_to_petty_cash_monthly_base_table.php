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
        Schema::table('petty_cash_monthly_base', function (Blueprint $table) {
            $table->string('branch')->nullable()->after('projectId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_monthly_base', function (Blueprint $table) {
            $table->dropColumn('branch');
        });
    }
};