<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('folders', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->text('descripcion')->nullable();
            $table->timestamp('creado')->useCurrent();
            $table->timestamp('actualizado')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('creado');
            $table->index('nombre');
        });
    }

    public function down()
    {
        Schema::dropIfExists('folders');
    }
};
