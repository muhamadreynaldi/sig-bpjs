<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penerimas', function (Blueprint $table) {
            // Force the 'lat' and 'lng' columns to accept null values
            $table->string('lat')->nullable()->change();
            $table->string('lng')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('penerimas', function (Blueprint $table) {
            // Revert back to not accepting null, if we need to rollback
            $table->string('lat')->nullable(false)->change();
            $table->string('lng')->nullable(false)->change();
        });
    }
};