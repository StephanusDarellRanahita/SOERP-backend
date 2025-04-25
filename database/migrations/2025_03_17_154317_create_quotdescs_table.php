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
        Schema::create('quotdescs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_quot');
            $table->string('desc');
            $table->string('parent')->nullable();
            $table->integer('qty');
            $table->string('unit');
            $table->float('price');
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->foreign('id_quot')->references('id')->on('quotations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotdescs');
    }
};
