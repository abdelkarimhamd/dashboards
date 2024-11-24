<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProjectManagerToProjectDetailsTable extends Migration
{
    public function up()
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->string('ProjectManager')->after('MainScope');  
        });
    }

    public function down()
    {
        Schema::table('project_details', function (Blueprint $table) {
            $table->dropColumn('ProjectManager'); 
        });
    }
}
