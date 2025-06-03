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
        Schema::create('invdescs', function (Blueprint $table) {
            $table->id('id_desc');
            $table->bigInteger('id_invoice')->unsigned();
            $table->integer('id');
            $table->string('desc')->nullable();
            $table->string('parent')->nullable();
            $table->integer('qty')->nullable();
            $table->string('unit')->nullable();
            $table->double('price')->nullable();
            $table->float('total');
            $table->string('remark')->nullable();
            $table->timestamps();

            $table->foreign('id_invoice')->references('id')->on('invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invdescs');
    }
};
