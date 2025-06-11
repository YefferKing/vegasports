<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->constrained('folders')->onDelete('cascade');
            $table->string('image_name');
            $table->integer('image_size');
            $table->string('image_type');
            $table->string('file_path');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->string('cloudinary_public_id');
            
            $table->index(['folder_id', 'updated_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('images');
    }
};
