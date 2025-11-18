<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('materi', function (Blueprint $table) {
            $table->id('id_materi');
            $table->unsignedBigInteger('id_course');
            $table->string('judul');
            $table->string('tipe'); // video / teori
            $table->string('url_video')->nullable();
            $table->text('teks_teori')->nullable();
            $table->timestamps();

            $table->foreign('id_course')->references('id_course')->on('kursus');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materis');
    }
};
