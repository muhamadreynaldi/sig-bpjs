<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatePenerimasTableForNewFeatures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('penerimas', function (Blueprint $table) {
            // Penambahan RT/RW setelah kolom 'alamat' (sesuaikan jika perlu)
            $table->string('rt', 3)->nullable()->after('alamat');
            $table->string('rw', 3)->nullable()->after('rt');

            // Penambahan Jenis Kepesertaan setelah kolom 'status'
            $table->string('jenis_kepesertaan')->nullable()->after('status');

            // Penambahan Bantuan Lainnya (dalam format JSON)
            $table->json('bantuan_lainnya')->nullable()->after('jenis_kepesertaan');
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
            $table->dropColumn(['rt', 'rw', 'jenis_kepesertaan', 'bantuan_lainnya']);
                        $table->string('lat')->nullable(false)->change();
            $table->string('lng')->nullable(false)->change();
        });
    }
}
