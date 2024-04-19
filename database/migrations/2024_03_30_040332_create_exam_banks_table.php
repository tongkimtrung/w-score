<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamBanksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_banks', function (Blueprint $table) {
            $table->id();
            $table->string('exam_bank_code')->unique();
            $table->string('exam_bank_name');
            $table->longText('exam_bank_content');
            $table->string('file_name');
            $table->string('file_size');
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
        Schema::dropIfExists('exam_banks');
    }
}
