<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMBProfessionalLearningsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_b__professional__learnings', function (Blueprint $table) {
            $table->id();
            $table->string('title_kh')->nullable();
            $table->string('title_en')->nullable();
            $table->enum('type', ['Glossary of CSE', 'List of IEC materials on CSE', 'List of additional resources for teachers', 'Comprehensive Sexuality Education (CSE)'])->nullable();
            $table->longText('image')->nullable();
            $table->longText('pdf')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('modified')->nullable();
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
        Schema::dropIfExists('m_b__professional_learnings');
    }
}
