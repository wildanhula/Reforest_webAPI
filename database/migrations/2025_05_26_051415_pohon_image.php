<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class pohonImage extends Migration
{
    public function up()
    {
        Schema::create('pohon_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pohon_id');
            $table->string('filename');
            $table->string('original_name');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->timestamps();
             $table->foreign('pohon_id')->references('id')->on('pohonku');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pohon_images');
    }
}