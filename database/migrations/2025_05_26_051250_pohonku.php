<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pohonku', function (Blueprint $table) {
            $table->id();
            $table->string('namaPohon', 255)->nullable()->default('text');
            $table->string('jenis_pohon', 255);
            $table->date('tanggal_tanam')->default(date('Y-m-d'));
            $table->decimal('lat', 9, 6);
            $table->decimal('long', 9, 6);
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pohonku');
    }
};
