<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGestationalAgeToPatientProgram extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_program', function ($table) {
            $table->decimal('gestational_age', 10, 2)->after('discontinue_reason_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_program', function (Blueprint $table) {
            $table->dropColumn('gestational_age');
        });
    }

}
