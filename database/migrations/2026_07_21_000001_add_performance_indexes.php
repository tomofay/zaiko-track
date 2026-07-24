<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('inventaris', function (Blueprint $table) {
            $table->index('id_barang');
            $table->index('id_ruangan');
        });

        Schema::table('barang', function (Blueprint $table) {
            $table->index('id_jenis_barang');
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->index('tgl_kembali');
            $table->index('tgl_pinjam');
        });

        Schema::table('notifikasi', function (Blueprint $table) {
            $table->index('id_users');
        });
    }

    public function down(): void
    {
        Schema::table('detail_peminjaman', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('inventaris', function (Blueprint $table) {
            $table->dropIndex(['id_barang']);
            $table->dropIndex(['id_ruangan']);
        });

        Schema::table('barang', function (Blueprint $table) {
            $table->dropIndex(['id_jenis_barang']);
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropIndex(['tgl_kembali']);
            $table->dropIndex(['tgl_pinjam']);
        });

        Schema::table('notifikasi', function (Blueprint $table) {
            $table->dropIndex(['id_users']);
        });
    }
};
