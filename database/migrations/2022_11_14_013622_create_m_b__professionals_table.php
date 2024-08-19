<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMBProfessionalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_b__professionals', function (Blueprint $table) {
            $table->id();
            $table->longText('image')->nullable();
            $table->string('title_kh')->unique()->nullable();
            $table->string('title_en')->unique()->nullable();
            $table->longText('content_kh')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('excerpt_kh')->nullable();
            $table->longText('excerpt_en')->nullable();
            $table->json('pdf')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('modified')->nullable();
            $table->text('tags')->nullable();
            $table->longText('slug_kh')->nullable();
            $table->longText('slug_en')->nullable();
            $table->integer('view')->nullable();
            $table->string('author')->default('RHAC');
            $table->enum('status', ['Draft', 'Publish'])->nullable();
            $table->json('grade')->nullable();
            $table->unsignedBigInteger('users_id')->nullable();
            $table->foreign('users_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('m_b__professionals');
    }
}
