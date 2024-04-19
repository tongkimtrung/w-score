<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->string('student_code');
            $table->string('candidate_number');  //số báo danh
            $table->unsignedBigInteger('exam_shift_detail_id');
            $table->foreign('exam_shift_detail_id')->references('id')->on('exam_shift_details')->onDelete('cascade');
            $table->string('student_name');
            $table->string('department_name');
            $table->string('total');
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
        Schema::dropIfExists('exam_results');
    }
}
