<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('eselon')->nullable()->after('jabatan');
            $table->string('unit_kerja')->nullable()->after('eselon');
            $table->string('satker')->nullable()->after('unit_kerja');
            $table->string('golongan')->nullable()->after('satker');
            $table->string('pangkat')->nullable()->after('golongan');
            $table->string('foto_url')->nullable()->after('pangkat');
            $table->longText('pegawai_raw')->nullable()->after('foto_url');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['eselon','unit_kerja','satker','golongan','pangkat','foto_url','pegawai_raw']);
        });
    }
};
