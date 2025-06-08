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
        Schema::create('penerimas', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('nama');
            $table->text('alamat')->nullable();
            $table->string('dusun')->nullable();
            $table->string('status');
            $table->decimal('lat', 10, 7);
            $table->decimal('lng', 10, 7);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penerimas');
    }
};