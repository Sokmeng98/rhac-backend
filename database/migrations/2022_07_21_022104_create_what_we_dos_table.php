<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatWeDosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('what_we_dos', function (Blueprint $table) {
            $table->id();
            $table->longText('icon');
            $table->string('title_kh')->unique()->nullable();
            $table->string('title_en')->unique()->nullable();
            $table->longText('subtitle_kh')->nullable();
            $table->longText('subtitle_en')->nullable();
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
        Schema::dropIfExists('what_we_dos');
    }
}