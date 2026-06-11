<?php

namespace App\Commands;

use App\Libraries\AstalaEmail;
use App\Models\NotifikasiModel;
use App\Models\PeminjamanModel;
use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use DateTimeImmutable;
use Throwable;

class CheckLoanDeadlines extends BaseCommand
{
    protected $group = 'ASTALA';
    protected $name = 'astala:check-loans';
    protected $description = 'Checks active loans for upcoming deadlines and overdue status.';

    public function run(array $params)
    {
        $now = new DateTimeImmutable('now');
        $oneHourLater = $now->modify('+1 hour');
        $reminders = $this->processUpcomingDeadlines($now, $oneHourLater);
        $overdue = $this->processOverdueLoans($now);

        CLI::write('Processed ' . $reminders . ' reminders and ' . $overdue . ' overdue loans.', 'green');
    }

    private function processUpcomingDeadlines(DateTimeImmutable $now, DateTimeImmutable $oneHourLater): int
    {
        $loans = $this->loanBuilder()
            ->where('p.status_peminjaman', 'aktif')
            ->where('p.reminder_sent', 0)
            ->where('p.tanggal_kembali_rencana >=', $now->format('Y-m-d H:i:s'))
            ->where('p.tanggal_kembali_rencana <=', $oneHourLater->format('Y-m-d H:i:s'))
            ->get()
            ->getResultArray();

        foreach ($loans as $loan) {
            $deadline = $this->formatDateTime($loan['tanggal_kembali_rencana']);
            $barang = $loan['nama_barang'] ?: 'Barang';
            $peminjam = $loan['user_nama'] ?: 'User';

            $this->notify((int) $loan['user_id'], 'Pengingat Pengembalian', 'Barang ' . $barang . ' harus dikembalikan dalam 1 jam (' . $deadline . ')', 'warning', '/peminjaman/current');
            $this->sendReminder($loan['user_email'] ?? '', $peminjam, $barang, $deadline, false);

            foreach ($this->admins() as $admin) {
                $item = $barang . ' (dipinjam oleh ' . $peminjam . ')';
                $this->notify((int) $admin['id'], 'Pengingat Deadline', 'Barang ' . $item . ' akan jatuh tempo dalam 1 jam', 'warning', '/admin/loans');
                $this->sendReminder($admin['email'] ?? '', $admin['nama'] ?? 'Admin', $item, $deadline, true);
            }

            (new PeminjamanModel())->update((int) $loan['id'], ['reminder_sent' => true]);
        }

        return count($loans);
    }

    private function processOverdueLoans(DateTimeImmutable $now): int
    {
        $loans = $this->loanBuilder()
            ->where('p.status_peminjaman', 'aktif')
            ->where('p.is_late', 0)
            ->where('p.tanggal_kembali_rencana <', $now->format('Y-m-d H:i:s'))
            ->get()
            ->getResultArray();

        foreach ($loans as $loan) {
            $deadline = $this->formatDateTime($loan['tanggal_kembali_rencana']);
            $barang = $loan['nama_barang'] ?: 'Barang';
            $peminjam = $loan['user_nama'] ?: 'User';

            (new PeminjamanModel())->update((int) $loan['id'], ['is_late' => true]);

            $this->notify((int) $loan['user_id'], 'TERLAMBAT', 'Barang ' . $barang . ' sudah melewati deadline pengembalian (' . $deadline . '). Segera kembalikan!', 'danger', '/peminjaman/current');
            $this->sendOverdue($loan['user_email'] ?? '', $peminjam, $barang, $deadline, false);

            foreach ($this->admins() as $admin) {
                $item = $barang . ' (dipinjam oleh ' . $peminjam . ')';
                $this->notify((int) $admin['id'], 'BARANG TERLAMBAT', 'Barang ' . $item . ' sudah melewati deadline', 'danger', '/admin/loans?is_late=true');
                $this->sendOverdue($admin['email'] ?? '', $admin['nama'] ?? 'Admin', $item, $deadline, true);
            }
        }

        return count($loans);
    }

    private function loanBuilder()
    {
        return db_connect()->table('peminjaman p')
            ->select('p.*, u.nama AS user_nama, u.email AS user_email, b.nama_barang')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('barang b', 'b.id = p.barang_id', 'left');
    }

    private function notify(int $userId, string $judul, string $pesan, string $tipe, string $link): void
    {
        (new NotifikasiModel())->insert([
            'user_id' => $userId,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
        ]);
    }

    private function sendReminder(string $email, string $nama, string $barang, string $deadline, bool $isAdmin): void
    {
        if ($email === '') {
            return;
        }

        try {
            (new AstalaEmail())->sendReminderEmail($email, $nama, $barang, $deadline, $isAdmin);
        } catch (Throwable $e) {
            log_message('error', 'Failed to send reminder email: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function sendOverdue(string $email, string $nama, string $barang, string $deadline, bool $isAdmin): void
    {
        if ($email === '') {
            return;
        }

        try {
            (new AstalaEmail())->sendOverdueEmail($email, $nama, $barang, $deadline, $isAdmin);
        } catch (Throwable $e) {
            log_message('error', 'Failed to send overdue email: {message}', ['message' => $e->getMessage()]);
        }
    }

    private function admins(): array
    {
        return (new UserModel())->where('role', 'admin')->findAll();
    }

    private function formatDateTime(?string $date): string
    {
        return $date ? date('d M Y H:i', strtotime($date)) : '-';
    }
}
