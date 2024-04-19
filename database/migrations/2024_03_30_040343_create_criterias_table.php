<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criterias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_bank_id');
            $table->foreign('exam_bank_id')->references('id')->on('exam_banks')->onDelete('cascade');
            $table->string('page')->nullable();
            $table->string('paragraph')->nullable();
            $table->longText('content');
            $table->string('property_name');
            $table->integer('property_type');
            $table->double('point');
            $table->integer('priority')->default(0); // độ ưu tiên
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
        Schema::dropIfExists('criterias');
    }
}
