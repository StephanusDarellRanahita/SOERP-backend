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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user')->unsigned();
            $table->bigInteger('id_quotation')->unsigned();
            $table->bigInteger('id_ticket')->unsigned();
            $table->string('invoice_id');
            $table->string('reff_requisition')->nullable();
            $table->string('equipment');
            $table->double('total');
            $table->string('status');
            $table->integer('rev');
            $table->longText('terms_conditions');
            $table->float('disc')->nullable();
            $table->string('disc_type')->nullable();
            $table->string('currency');
            $table->timestamp('valid_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
