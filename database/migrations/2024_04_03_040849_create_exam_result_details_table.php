<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamResultDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_result_details', function (Blueprint $table) {
            $table->id();
            $table->string('student_code')->nullable();
            $table->string('exam_bank_name')->nullable();
            $table->string('department_name')->nullable();
            $table->string('criteria_id')->nullable();
            $table->string('parent_criteria_id')->nullable();
            $table->string('parent_criteria_name')->nullable();
            $table->boolean('has_child')->nullable();
            $table->string('exam_shift_name')->nullable();
            $table->string('exam_shift_detail_id')->nullable();
            $table->string('candidate_number')->nullable();
            $table->string('property_name')->nullable();
            $table->string('point')->nullable();
            $table->string('real_point')->nullable();
            $table->boolean('flag')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_result_details');
    }
}
