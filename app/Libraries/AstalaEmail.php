<?php

namespace App\Libraries;

class AstalaEmail
{
    public function sendReminderEmail(string $email, string $nama, string $barang, string $deadline, bool $isAdmin = false): bool
    {
        $subject = 'Pengingat: Pengembalian Barang 1 Jam Lagi - ASTALA';
        $intro = $isAdmin
            ? 'Ini adalah pengingat bahwa barang berikut akan jatuh tempo pengembalian dalam 1 jam:'
            : 'Barang yang Anda pinjam akan jatuh tempo pengembalian dalam 1 jam:';

        return $this->send($email, $subject, $this->layout('Pengingat Pengembalian', $nama, $intro, $barang, 'Deadline: ' . $deadline, 'Mohon segera lakukan pengembalian untuk menghindari keterlambatan.', '#fd7e14'));
    }

    public function sendOverdueEmail(string $email, string $nama, string $barang, string $deadline, bool $isAdmin = false): bool
    {
        $subject = 'PERINGATAN: Barang Terlambat Dikembalikan - ASTALA';
        $intro = $isAdmin
            ? 'Barang berikut sudah melewati deadline pengembalian:'
            : 'Barang yang Anda pinjam sudah melewati deadline pengembalian:';

        return $this->send($email, $subject, $this->layout('Barang Terlambat', $nama, $intro, $barang, 'Deadline: ' . $deadline, 'Mohon segera kembalikan barang tersebut ke gudang.', '#dc3545'));
    }

    public function sendApprovalEmail(string $email, string $nama, string $barang, string $status, string $catatan = ''): bool
    {
        $approved = $status === 'approved';
        $message = 'Permintaan peminjaman Anda untuk barang berikut telah ' . ($approved ? 'disetujui.' : 'ditolak.');
        $footer = $catatan !== '' ? 'Catatan: ' . $catatan : ($approved ? 'Silakan login ke ASTALA untuk melanjutkan proses peminjaman.' : '');

        return $this->send($email, 'Peminjaman ' . ($approved ? 'Disetujui' : 'Ditolak') . ' - ASTALA', $this->layout('Pemberitahuan Peminjaman', $nama, $message, $barang, strtoupper($approved ? 'Disetujui' : 'Ditolak'), $footer, $approved ? '#28a745' : '#dc3545'));
    }

    private function send(string $to, string $subject, string $html): bool
    {
        if (! $this->isConfigured()) {
            log_message('warning', 'Email not configured. Skipping email to {to}', ['to' => $to]);

            return false;
        }

        $email = service('email');
        $email->initialize([
            'protocol' => 'smtp',
            'SMTPHost' => env('EMAIL_HOST'),
            'SMTPPort' => (int) env('EMAIL_PORT', 587),
            'SMTPUser' => env('EMAIL_USER'),
            'SMTPPass' => env('EMAIL_PASSWORD'),
            'SMTPCrypto' => (string) env('EMAIL_CRYPTO', ''),
            'mailType' => 'html',
            'charset' => 'utf-8',
            'wordWrap' => true,
        ]);

        $email->setFrom((string) env('EMAIL_USER'), 'ASTALA - PT Lintasarta');
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($html);

        return $email->send(false);
    }

    private function isConfigured(): bool
    {
        return (string) env('EMAIL_HOST') !== ''
            && (string) env('EMAIL_PORT') !== ''
            && (string) env('EMAIL_USER') !== ''
            && (string) env('EMAIL_PASSWORD') !== '';
    }

    private function layout(string $title, string $nama, string $intro, string $barang, string $highlight, string $footer, string $color): string
    {
        return '
            <!doctype html>
            <html>
            <body style="font-family:Segoe UI,Tahoma,Verdana,sans-serif;background:#f3f4f6;margin:0;padding:24px">
                <div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden">
                    <div style="background:' . esc($color, 'attr') . ';color:#ffffff;padding:28px;text-align:center">
                        <h1 style="margin:0;font-size:24px">ASTALA</h1>
                        <p style="margin:8px 0 0">' . esc($title) . '</p>
                    </div>
                    <div style="padding:28px;color:#111827">
                        <p>Halo <strong>' . esc($nama) . '</strong>,</p>
                        <p>' . esc($intro) . '</p>
                        <p style="font-size:18px;font-weight:700">' . esc($barang) . '</p>
                        <div style="margin:18px 0;padding:14px;border-radius:10px;background:#f9fafb;color:' . esc($color, 'attr') . ';font-weight:700;text-align:center">' . esc($highlight) . '</div>
                        <p>' . esc($footer) . '</p>
                    </div>
                    <div style="padding:16px;text-align:center;color:#6b7280;font-size:12px">ASTALA - PT Lintasarta</div>
                </div>
            </body>
            </html>';
    }
}
