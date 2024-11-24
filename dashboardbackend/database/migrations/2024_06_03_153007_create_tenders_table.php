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
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('tenderTitle')->nullable();
            $table->string('tenderNumber')->nullable();
            $table->string('employerName')->nullable();
            $table->string('location')->nullable();
            $table->string('selectedOption')->nullable();
            $table->string('sourceOption')->nullable();
            $table->integer('estimatedNbr')->nullable();
            $table->string('companyPreQuilifiedOption')->nullable();
            $table->string('contactDuration')->nullable();
            $table->text('scopeServices')->nullable();
            $table->date('submissionDate')->nullable();
            $table->date('startDate')->nullable();
            $table->string('contractType')->nullable();
            $table->date('receivedDate')->nullable();
            $table->date('jobexDate')->nullable();
            $table->date('Q_ADate')->nullable();
            $table->date('extinsionDate')->nullable();
            $table->date('siteVisitDate')->nullable();
            $table->decimal('estimatedMargin', 8, 2)->nullable();
            $table->integer('validityPeriod')->nullable();
            $table->text('conditions')->nullable();
            $table->text('positionRecommendation')->nullable();
            $table->string('currencyOptions')->nullable();
            $table->decimal('performanceBond', 8, 2)->nullable();
            $table->string('retention')->nullable();
            $table->string('languageOptions')->nullable();
            $table->boolean('trfProcess')->default(false);
            $table->boolean('rfpSubmitted')->default(false);
            $table->string('rfpDocument')->nullable();
            $table->string('projectName')->nullable(); // New field
            $table->string('companyOrPartnershipName')->nullable(); // New field
            $table->string('contractPeriod')->nullable(); // New field
            $table->date('dateTenderReceived')->nullable(); // New field
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
