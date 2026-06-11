<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAstalaSchema extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100],
            'email' => ['type' => 'VARCHAR', 'constraint' => 100],
            'password' => ['type' => 'VARCHAR', 'constraint' => 255],
            'no_hp' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'role' => ['type' => 'ENUM', 'constraint' => ['admin', 'mitra', 'karyawan', 'manager'], 'default' => 'karyawan'],
            'sub_user' => ['type' => 'ENUM', 'constraint' => ['editor', 'viewer'], 'default' => 'viewer'],
            'is_verified' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'foto' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'dashboard_preferences' => ['type' => 'JSON', 'null' => true],
            'theme' => ['type' => 'ENUM', 'constraint' => ['light', 'dark', 'system'], 'default' => 'system'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->createTable('users', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama_barang' => ['type' => 'VARCHAR', 'constraint' => 200],
            'nomor_seri' => ['type' => 'VARCHAR', 'constraint' => 100],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'kategori' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'lokasi_penyimpanan' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'status_kondisi' => ['type' => 'ENUM', 'constraint' => ['baik', 'rusak'], 'default' => 'baik'],
            'status_ketersediaan' => ['type' => 'ENUM', 'constraint' => ['tersedia', 'dipinjam'], 'default' => 'tersedia'],
            'wajib_qr' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('nomor_seri');
        $this->forge->createTable('barang', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100],
            'lokasi' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['indoor', 'outdoor'], 'default' => 'indoor'],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('gudang', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'barang_id' => ['type' => 'INT', 'unsigned' => true],
            'lokasi_peminjaman' => ['type' => 'VARCHAR', 'constraint' => 300],
            'keperluan' => ['type' => 'TEXT', 'null' => true],
            'tanggal_pinjam' => ['type' => 'DATETIME'],
            'tanggal_kembali_rencana' => ['type' => 'DATETIME'],
            'tanggal_kembali_aktual' => ['type' => 'DATETIME', 'null' => true],
            'status_peminjaman' => ['type' => 'ENUM', 'constraint' => ['aktif', 'selesai', 'dibatalkan'], 'default' => 'aktif'],
            'is_late' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'reminder_sent' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('barang_id', 'barang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('peminjaman', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 100],
            'description' => ['type' => 'TEXT', 'null' => true],
            'entity_type' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'entity_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'ip_address' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'user_agent' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('activity_logs', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true],
            'judul' => ['type' => 'VARCHAR', 'constraint' => 200],
            'pesan' => ['type' => 'TEXT'],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['info', 'success', 'warning', 'danger'], 'default' => 'info'],
            'link' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
            'is_read' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('notifikasi', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'barang_id' => ['type' => 'INT', 'unsigned' => true],
            'foto_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'urutan' => ['type' => 'INT', 'default' => 1],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('barang_id', 'barang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('foto_barang', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'peminjaman_id' => ['type' => 'INT', 'unsigned' => true],
            'foto_path' => ['type' => 'VARCHAR', 'constraint' => 500],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['pinjam', 'kembali']],
            'barcode_detected' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('peminjaman_id', 'peminjaman', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('foto_peminjaman', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'gudang_id' => ['type' => 'INT', 'unsigned' => true],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['kabel', 'odp', 'closure']],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100],
            'core' => ['type' => 'INT'],
            'stok_roll' => ['type' => 'INT', 'default' => 0],
            'stok_meter' => ['type' => 'INT', 'default' => 0],
            'meter_per_roll' => ['type' => 'INT', 'default' => 4000],
            'stok_pcs' => ['type' => 'INT', 'default' => 0],
            'deskripsi' => ['type' => 'TEXT', 'null' => true],
            'min_stok_roll' => ['type' => 'INT', 'default' => 0],
            'min_stok_pcs' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('gudang_id', 'gudang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('aset_material', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'mitra_id' => ['type' => 'INT', 'unsigned' => true],
            'gudang_id' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['request', 'waiting', 'pickup', 'confirmation', 'done', 'rejected'], 'default' => 'request'],
            'nama_mitra' => ['type' => 'VARCHAR', 'constraint' => 100],
            'nama_petugas' => ['type' => 'VARCHAR', 'constraint' => 100],
            'deskripsi_keperluan' => ['type' => 'TEXT', 'null' => true],
            'alasan_penolakan' => ['type' => 'TEXT', 'null' => true],
            'tanggal_request' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_approval' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_pickup' => ['type' => 'DATETIME', 'null' => true],
            'tanggal_done' => ['type' => 'DATETIME', 'null' => true],
            'ttd_petugas' => ['type' => 'TEXT', 'null' => true],
            'ttd_admin' => ['type' => 'TEXT', 'null' => true],
            'approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('mitra_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('gudang_id', 'gudang', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('approved_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('pengambilan_aset', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pengambilan_id' => ['type' => 'INT', 'unsigned' => true],
            'aset_id' => ['type' => 'INT', 'unsigned' => true],
            'jumlah_roll' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'jumlah_meter' => ['type' => 'INT', 'default' => 0],
            'jumlah_pcs' => ['type' => 'INT', 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pengambilan_id', 'pengambilan_aset', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('aset_id', 'aset_material', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('pengambilan_item', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'pengambilan_id' => ['type' => 'INT', 'unsigned' => true],
            'item_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'foto_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'tipe' => ['type' => 'ENUM', 'constraint' => ['kabel_ujung1', 'kabel_ujung2', 'kabel_roll', 'odp', 'closure']],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pengambilan_id', 'pengambilan_aset', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('item_id', 'pengambilan_item', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('foto_pengambilan', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'aset_id' => ['type' => 'INT', 'unsigned' => true],
            'tipe_aktivitas' => ['type' => 'ENUM', 'constraint' => ['masuk', 'keluar']],
            'jumlah_roll' => ['type' => 'DECIMAL', 'constraint' => '10,2', 'default' => 0],
            'jumlah_meter' => ['type' => 'INT', 'default' => 0],
            'jumlah_pcs' => ['type' => 'INT', 'default' => 0],
            'pengambilan_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'foto_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'keterangan' => ['type' => 'TEXT', 'null' => true],
            'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('aset_id', 'aset_material', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('pengambilan_id', 'pengambilan_aset', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('stok_history', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'aset_id' => ['type' => 'INT', 'unsigned' => true],
            'foto_path' => ['type' => 'VARCHAR', 'constraint' => 255],
            'is_primary' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('aset_id', 'aset_material', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('foto_aset_material', true);
    }

    public function down()
    {
        foreach ([
            'foto_aset_material',
            'stok_history',
            'foto_pengambilan',
            'pengambilan_item',
            'pengambilan_aset',
            'aset_material',
            'foto_peminjaman',
            'foto_barang',
            'notifikasi',
            'activity_logs',
            'peminjaman',
            'gudang',
            'barang',
            'users',
        ] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
