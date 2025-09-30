<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacy_infos', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address');
            $table->string('zip', 5);
            $table->string('city');
            $table->string('phone', 20);
            $table->string('email');
            $table->string('siret', 14);
            $table->string('vat_number', 13)->nullable();
            $table->string('license_number');
            $table->json('logo')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacy_infos');
    }
};
