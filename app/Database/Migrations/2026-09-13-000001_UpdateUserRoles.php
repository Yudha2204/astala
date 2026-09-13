<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateUserRoles extends Migration
{
    public function up()
    {
        $db = db_connect();

        // 1. Expand ENUM temporarily to allow 'pj_gudang' alongside old values
        $db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'mitra', 'karyawan', 'manager', 'pj_gudang') NOT NULL DEFAULT 'karyawan'");

        // 2. Migrate existing records:
        // - 'karyawan' becomes 'pj_gudang'
        // - 'mitra' becomes 'karyawan'
        // - 'manager' (if any) becomes 'pj_gudang'
        $db->query("UPDATE `users` SET `role` = 'pj_gudang', `sub_user` = 'editor' WHERE `role` = 'karyawan'");
        $db->query("UPDATE `users` SET `role` = 'pj_gudang', `sub_user` = 'editor' WHERE `role` = 'manager'");
        $db->query("UPDATE `users` SET `role` = 'karyawan', `sub_user` = 'viewer' WHERE `role` = 'mitra'");

        // 3. Constrain ENUM strictly to the 3 new roles: admin, pj_gudang, karyawan
        $db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'pj_gudang', 'karyawan') NOT NULL DEFAULT 'karyawan'");
    }

    public function down()
    {
        $db = db_connect();

        // Expand to include previous roles
        $db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'mitra', 'karyawan', 'manager', 'pj_gudang') NOT NULL DEFAULT 'karyawan'");

        // Revert records
        $db->query("UPDATE `users` SET `role` = 'mitra' WHERE `role` = 'karyawan'");
        $db->query("UPDATE `users` SET `role` = 'karyawan' WHERE `role` = 'pj_gudang'");

        // Revert ENUM
        $db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'mitra', 'karyawan', 'manager') NOT NULL DEFAULT 'karyawan'");
    }
}