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
        Schema::create('registrasi_kursus', function (Blueprint $table) {
            $table->id(); // PK tambahan
            $table->unsignedBigInteger('id_admin');
            $table->unsignedBigInteger('id_member');
            $table->unsignedBigInteger('id_course');
            $table->date('tgl_trans');
            $table->string('metode_bayar');
            $table->integer('total_byr');
            $table->string('bukti_byr')->nullable();
            $table->integer('progress')->default(0);
            $table->string('level');
            $table->timestamps();

            $table->foreign('id_admin')->references('id_admin')->on('admin');
            $table->foreign('id_member')->references('id_member')->on('member');
            $table->foreign('id_course')->references('id_course')->on('kursus');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrasi_kursuses');
    }
};
