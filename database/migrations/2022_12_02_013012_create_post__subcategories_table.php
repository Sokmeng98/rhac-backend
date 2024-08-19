<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostSubcategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post__subcategories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->nullable();
            $table->string('name_en')->unique()->nullable();
            $table->mediumText('slug_kh')->nullable();
            $table->string('slug_en', 255)->nullable();
            $table->integer('post_count')->nullable();
            $table->unsignedBigInteger('post__categories_id')->nullable();
            $table->foreign('post__categories_id')->references('id')->on('post__categories')->onDelete('cascade');
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
        Schema::dropIfExists('post__subcategories');
    }
}
