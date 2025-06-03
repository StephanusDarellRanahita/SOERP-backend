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
        Schema::create('pas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_user')->unsigned();
            $table->bigInteger('reff_inv')->unsigned()->nullable();
            $table->string('pa_id');
            $table->string("desc");
            $table->string('category');
            $table->string("project");
            $table->string("operation_device")->nullable();
            $table->string("remark");
            $table->float('total');
            $table->timestamps();

            $table->foreign('id_user')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pas');
    }
};
