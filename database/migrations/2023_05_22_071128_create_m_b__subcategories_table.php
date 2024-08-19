<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMBSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_b__subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->nullable();
            $table->string('name_en')->unique()->nullable();
            $table->mediumText('slug_kh')->nullable();
            $table->string('slug_en', 255)->nullable();
            $table->integer('count')->nullable();
            $table->longText('img')->nullable();
            $table->unsignedBigInteger('m_b__categories_id')->nullable();
            $table->foreign('m_b__categories_id')->references('id')->on('m_b__categories')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_b__subcategories');
    }
}
