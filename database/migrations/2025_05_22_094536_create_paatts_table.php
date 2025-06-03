<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('paatts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_pa')->unsigned();
            $table->string('path');
            $table->string('name');
            $table->timestamps();

            $table->foreign('id_pa')->references('id')->on('pas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paatts');
    }
};
