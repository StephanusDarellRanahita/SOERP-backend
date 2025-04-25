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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_client');
            $table->unsignedBigInteger('id_ticket');
            $table->string('quot_id');
            $table->string('reff_requisition')->nullable();
            $table->string('equipment');
            $table->float('total');
            $table->string('status');
            $table->integer('rev');
            $table->text('terms_condition');
            $table->timestamps();

            $table->foreign('id_client')->references('id')->on('clients')->onDelete('restrict');
            $table->foreign('id_ticket')->references('id')->on('tickets')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
