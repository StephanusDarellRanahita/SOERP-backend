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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('office_phone')->nullable();
            $table->string('business')->nullable();
            $table->string('npwp')->nullable();
            $table->string('website')->nullable();
            $table->string('pic_1');
            $table->string('rule_1')->nullable();
            $table->string('email_1')->nullable();
            $table->string('phone_1')->nullable();
            $table->string('pic_2')->nullable();
            $table->string('rule_2')->nullable();
            $table->string('email_2')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
