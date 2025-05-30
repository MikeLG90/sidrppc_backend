<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('solicitudes_resoluciones', function (Blueprint $table) {
            $table->id();
            $table->string('promovente');
            $table->unsignedBigInteger('oficina_id');
            $table->text('descripcion');
            $table->timestamps();
            
            $table->foreign('oficina_id')->references('oficina_id')->on('oficinas')->onDelete('cascade');
        });
    }
    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('solicitudes_resoluciones');
    }
};
