<?php

namespace App\Libraries;

use Dompdf\Dompdf;
use Dompdf\Options;

class BarangTemplatePdf
{
    public function render(array $rows): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Calibri');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($this->html($rows));
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    private function html(array $rows): string
    {
        $logo = $this->imageDataUri(FCPATH . 'img' . DIRECTORY_SEPARATOR . 'logo_lintasarta.png')
            ?? $this->imageDataUri(FCPATH . 'logo_lintasarta.png')
            ?? $this->imageDataUri(FCPATH . 'img' . DIRECTORY_SEPARATOR . 'logo_astala.png')
            ?? $this->imageDataUri(FCPATH . 'logo_astala.png')
            ?? '';

        $bodyRows = '';
        foreach ($rows as $row) {
            $photo = $this->imageDataUri($row['foto'] ?? null);
            $photoHtml = $photo
                ? '<img class="item-photo" src="' . $photo . '" alt="Foto Barang">'
                : '<div class="photo-empty"></div>';

            $bodyRows .= '<tr>'
                . '<td class="number">' . $this->esc($row['no'] ?? '-') . '</td>'
                . '<td class="photo-cell">' . $photoHtml . '</td>'
                . '<td>' . $this->esc($row['nama'] ?? '-') . '</td>'
                . '<td>' . $this->esc($row['sn'] ?? '-') . '</td>'
                . '<td class="notes">'
                . '<div>Kondisi : ' . $this->esc($row['kondisi'] ?? '-') . '</div>'
                . '<div>Lokasi: ' . $this->esc($row['lokasi'] ?? '-') . '</div>'
                . '<div>Status: ' . $this->esc($row['ketersediaan'] ?? '-') . '</div>'
                . '<div>Kategori: ' . $this->esc($row['kategori'] ?? '-') . '</div>'
                . '<div>Tanggal Input: ' . $this->esc($row['tanggal'] ?? '-') . '</div>'
                . '</td>'
                . '</tr>';
        }

        return '<!doctype html><html><head><meta charset="utf-8"><style>'
            . '@page{size:letter;margin:38px 34px 0 34px;}'
            . 'body{font-family:Calibri,Arial,sans-serif;color:#000;font-size:12pt;}'
            . '.header{position:relative;height:82px;margin-bottom:2px;}'
            . '.logo{position:absolute;left:0;top:0;width:150px;height:auto;}'
            . 'h1{text-align:center;font-size:12pt;text-decoration:underline;margin:52px 0 18px 0;font-weight:bold;}'
            . 'table{width:100%;border-collapse:collapse;table-layout:fixed;margin-left:-6px;}'
            . 'th,td{border:1px solid #000;padding:5px 6px;vertical-align:middle;font-size:12pt;line-height:1.2;}'
            . 'th{text-align:center;font-weight:bold;}'
            . '.number{width:6%;text-align:center;}'
            . '.photo-col{width:17%;}.name-col{width:18%;}.serial-col{width:18%;}.notes-col{width:41%;}'
            . '.photo-cell{text-align:center;height:108px;}'
            . '.item-photo{width:100px;height:100px;object-fit:fill;}'
            . '.photo-empty{width:100px;height:100px;display:inline-block;}'
            . '.notes div{margin:0 0 5px 0;}'
            . '</style></head><body>'
            . '<div class="header">' . ($logo ? '<img class="logo" src="' . $logo . '" alt="ASTALA">' : '') . '</div>'
            . '<h1>DAFTAR BARANG LINTASARTA BATAM</h1>'
            . '<table><thead><tr>'
            . '<th class="number">No.</th>'
            . '<th class="photo-col">Foto Barang</th>'
            . '<th class="name-col">Nama Barang</th>'
            . '<th class="serial-col">Serial Number</th>'
            . '<th class="notes-col">Keterangan</th>'
            . '</tr></thead><tbody>' . $bodyRows . '</tbody></table>'
            . '</body></html>';
    }

    private function imageDataUri(?string $path): ?string
    {
        if (! is_string($path) || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';
        if ($mime === 'image/avif' && function_exists('imagecreatefromavif')) {
            $image = imagecreatefromavif($path);
            if (! $image) {
                return null;
            }

            ob_start();
            imagepng($image);
            imagedestroy($image);
            $png = ob_get_clean();

            return $png ? 'data:image/png;base64,' . base64_encode($png) : null;
        }

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'], true)) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path) ?: '');
    }

    private function esc($value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
