<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMbLearnerCategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_b__learner_category', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('m_b__learner_id')->nullable();
            $table->foreign('m_b__learner_id')->references('id')->on('m_b__learners')->onDelete('cascade');
            $table->unsignedBigInteger('m_b__category_id')->nullable();
            $table->foreign('m_b__category_id')->references('id')->on('m_b__categories')->onDelete('cascade');
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
        Schema::dropIfExists('mb_learner_category');
    }
}
