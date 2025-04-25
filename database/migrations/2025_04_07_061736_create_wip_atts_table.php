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
        Schema::create('wip_atts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_wip');
            $table->unsignedBigInteger('id_user');
            $table->string('desc');
            $table->timestamps();

            $table->foreign('id_wip')->references('id')->on('wips')->onDelete('restrict');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wip_atts');
    }
};
