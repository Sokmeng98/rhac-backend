<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMBLearnerSubcategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_b__learner_subcategory', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('m_b__learner_id')->nullable();
            $table->foreign('m_b__learner_id')->references('id')->on('m_b__learners')->onDelete('cascade');
            $table->unsignedBigInteger('m_b__subcategory_id')->nullable();
            $table->foreign('m_b__subcategory_id')->references('id')->on('m_b__subcategories')->onDelete('cascade');
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
        Schema::dropIfExists('m_b__learner_subcategory');
    }
}
