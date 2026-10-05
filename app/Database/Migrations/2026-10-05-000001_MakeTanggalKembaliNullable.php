<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class MakeTanggalKembaliNullable extends Migration
{
    public function up()
    {
        $db = db_connect();
        try {
            $db->query("ALTER TABLE `peminjaman` MODIFY COLUMN `tanggal_kembali_rencana` DATETIME NULL DEFAULT NULL");
        } catch (\Throwable) {
        }
    }

    public function down()
    {
        $db = db_connect();
        try {
            $db->query("ALTER TABLE `peminjaman` MODIFY COLUMN `tanggal_kembali_rencana` DATETIME NOT NULL");
        } catch (\Throwable) {
        }
    }
}
