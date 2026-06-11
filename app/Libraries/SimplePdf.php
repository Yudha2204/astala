<?php

namespace App\Libraries;

class SimplePdf
{
    private int $width = 842;
    private int $height = 595;
    private int $margin = 36;

    public function render(string $title, array $headers, array $rows): string
    {
        $pages = $this->buildPages($title, $headers, $rows);
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $kids = [];

        foreach ($pages as $content) {
            $pageRef = max(array_keys($objects)) + 1;
            $contentRef = $pageRef + 1;
            $kids[] = $pageRef . ' 0 R';
            $objects[$pageRef] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 ' . $this->width . ' ' . $this->height . '] /Resources << /Font << /F1 3 0 R >> >> /Contents ' . $contentRef . ' 0 R >>';
            $objects[$contentRef] = '<< /Length ' . strlen($content) . " >>\nstream\n" . $content . "\nendstream";
        }

        $objects[2] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . count($kids) . ' >>';
        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $number => $body) {
            $offsets[$number] = strlen($pdf);
            $pdf .= $number . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF";

        return $pdf;
    }

    private function buildPages(string $title, array $headers, array $rows): array
    {
        $pages = [];
        $content = '';
        $y = $this->height - $this->margin;
        $page = 1;
        $columns = max(1, count($headers));
        $contentWidth = $this->width - ($this->margin * 2);
        $columnWidth = $contentWidth / $columns;

        $startPage = function () use (&$content, &$y, &$page, $title, $headers, $columnWidth): void {
            $content = '';
            $y = $this->height - $this->margin;
            $content .= $this->text($this->margin, $y, 16, 'ASTALA - PT Lintasarta');
            $y -= 20;
            $content .= $this->text($this->margin, $y, 12, $title);
            $content .= $this->text($this->width - 135, $y, 8, 'Halaman ' . $page);
            $y -= 18;
            $content .= $this->text($this->margin, $y, 8, 'Dicetak: ' . date('d M Y H:i'));
            $y -= 20;

            foreach ($headers as $index => $header) {
                $content .= $this->text($this->margin + ($index * $columnWidth), $y, 7, $this->fit((string) $header, $columnWidth, 7));
            }
            $y -= 5;
            $content .= $this->line($this->margin, $y, $this->width - $this->margin, $y);
            $y -= 12;
        };

        $startPage();

        foreach ($rows as $row) {
            if ($y < $this->margin + 24) {
                $pages[] = $content;
                $page++;
                $startPage();
            }

            $values = array_values($row);
            foreach ($headers as $index => $_) {
                $value = (string) ($values[$index] ?? '');
                $content .= $this->text($this->margin + ($index * $columnWidth), $y, 7, $this->fit($value, $columnWidth, 7));
            }
            $y -= 12;
        }

        if ($rows === []) {
            $content .= $this->text($this->margin, $y, 9, 'Tidak ada data.');
        }

        $pages[] = $content;

        return $pages;
    }

    private function text(float $x, float $y, int $size, string $text): string
    {
        return "BT /F1 {$size} Tf " . round($x, 2) . ' ' . round($y, 2) . ' Td (' . $this->escape($text) . ") Tj ET\n";
    }

    private function line(float $x1, float $y1, float $x2, float $y2): string
    {
        return round($x1, 2) . ' ' . round($y1, 2) . ' m ' . round($x2, 2) . ' ' . round($y2, 2) . " l S\n";
    }

    private function fit(string $text, float $width, int $size): string
    {
        $text = preg_replace('/\s+/', ' ', trim($text)) ?? '';
        $max = max(4, (int) floor($width / ($size * 0.52)));

        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, max(1, $max - 3)) . '...';
    }

    private function escape(string $text): string
    {
        $text = iconv('UTF-8', 'Windows-1252//TRANSLIT//IGNORE', $text) ?: $text;
        $text = preg_replace('/[^\x09\x0A\x0D\x20-\x7E\x80-\xFF]/', '', $text) ?? '';

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
