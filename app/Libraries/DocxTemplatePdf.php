<?php

namespace App\Libraries;

use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use ReflectionProperty;
use RuntimeException;
use Throwable;

class DocxTemplatePdf
{
    public function render(string $templatePath, array $scalars, array $blocks, string $filename, array $imageFields = []): array
    {
        if (! is_file($templatePath)) {
            throw new RuntimeException('Template DOCX tidak ditemukan: ' . $templatePath);
        }

        $previousOpen = $this->macroChars('macroOpeningChars');
        $previousClose = $this->macroChars('macroClosingChars');
        $this->setMacroChars('{', '}');

        $docxPath = $this->tempPath('docx');
        $pdfPath = $this->tempPath('pdf');

        try {
            $processor = new TemplateProcessor($templatePath);
            $this->normalizeDocxtemplaterSyntax($processor);

            foreach ($scalars as $key => $value) {
                $processor->setValue((string) $key, $this->stringValue($value));
            }

            foreach ($blocks as $block => $rows) {
                $this->cloneBlock($processor, (string) $block, array_values($rows), $imageFields[(string) $block] ?? []);
            }

            $this->removeUnresolvedMacros($processor);
            $processor->saveAs($docxPath);
            $this->convertDocxToPdf($docxPath, $pdfPath);

            return [
                'bytes' => file_get_contents($pdfPath) ?: '',
                'filename' => $filename . '-' . date('Ymd-His') . '.pdf',
            ];
        } finally {
            $this->setMacroChars($previousOpen, $previousClose);
            @unlink($docxPath);
            @unlink($pdfPath);
        }
    }

    private function convertDocxToPdf(string $docxPath, string $pdfPath): void
    {
        $soffice = $this->libreOfficePath();
        if ($soffice !== null) {
            $outDir = dirname($pdfPath);
            $command = '"' . $soffice . '" --headless --convert-to pdf --outdir ' . escapeshellarg($outDir) . ' ' . escapeshellarg($docxPath);
            exec($command, $output, $code);

            $converted = $outDir . DIRECTORY_SEPARATOR . basename($docxPath, '.docx') . '.pdf';
            if ($code === 0 && is_file($converted)) {
                rename($converted, $pdfPath);

                return;
            }
        }

        Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, ROOTPATH . 'vendor' . DIRECTORY_SEPARATOR . 'dompdf' . DIRECTORY_SEPARATOR . 'dompdf');
        $phpWord = IOFactory::load($docxPath);
        IOFactory::createWriter($phpWord, 'PDF')->save($pdfPath);

        if (! is_file($pdfPath) || filesize($pdfPath) === 0) {
            throw new RuntimeException('Gagal mengubah DOCX menjadi PDF');
        }
    }

    private function normalizeDocxtemplaterSyntax(TemplateProcessor $processor): void
    {
        $this->mutateParts($processor, static function (string $xml): string {
            $xml = preg_replace('/\{#([A-Za-z0-9_]+)\}/', '{$1}', $xml) ?? $xml;
            $xml = preg_replace('/\{%([A-Za-z0-9_]+)\}/', '{$1}', $xml) ?? $xml;

            return $xml;
        });
    }

    private function cloneBlock(TemplateProcessor $processor, string $block, array $rows, array $imageFields): void
    {
        if ($rows === []) {
            $processor->cloneBlock($block, 0, true, false);

            return;
        }

        if ($imageFields === []) {
            $processor->cloneBlock($block, count($rows), true, false, array_map(
                fn (array $row): array => array_map(fn ($value): string => $this->stringValue($value), $row),
                $rows
            ));

            return;
        }

        $processor->cloneBlock($block, count($rows), true, true);

        foreach ($rows as $index => $row) {
            $suffix = '#' . ($index + 1);
            foreach ($row as $key => $value) {
                $macro = (string) $key . $suffix;
                if (! array_key_exists((string) $key, $imageFields)) {
                    $processor->setValue($macro, $this->stringValue($value));
                    continue;
                }

                if (! is_string($value) || ! is_file($value) || ! $this->supportedImage($value)) {
                    $processor->setValue($macro, '');
                    continue;
                }

                $processor->setImageValue($macro, [
                    'path' => $value,
                    'width' => $imageFields[$key]['width'] ?? 100,
                    'height' => $imageFields[$key]['height'] ?? 100,
                    'ratio' => $imageFields[$key]['ratio'] ?? false,
                ]);
            }
        }
    }

    private function removeUnresolvedMacros(TemplateProcessor $processor): void
    {
        $this->mutateParts($processor, static function (string $xml): string {
            return preg_replace('/\{[#\/%]?[A-Za-z0-9_]+(?:#[0-9]+)?\}/', '', $xml) ?? $xml;
        });
    }

    private function mutateParts(TemplateProcessor $processor, callable $mutator): void
    {
        foreach (['tempDocumentMainPart', 'tempDocumentHeaders', 'tempDocumentFooters'] as $propertyName) {
            $property = new ReflectionProperty($processor, $propertyName);
            $property->setAccessible(true);
            $value = $property->getValue($processor);

            if (is_string($value)) {
                $property->setValue($processor, $mutator($value));
                continue;
            }

            if (is_array($value)) {
                foreach ($value as $key => $part) {
                    if (is_string($part)) {
                        $value[$key] = $mutator($part);
                    }
                }
                $property->setValue($processor, $value);
            }
        }
    }

    private function libreOfficePath(): ?string
    {
        $candidates = array_filter([
            env('LIBREOFFICE_PATH'),
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function stringValue($value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        return str_replace(["\r\n", "\r"], "\n", (string) $value);
    }

    private function supportedImage(string $path): bool
    {
        $image = @getimagesize($path);
        if (! is_array($image)) {
            return false;
        }

        return in_array($image['mime'] ?? '', ['image/jpeg', 'image/png', 'image/bmp', 'image/gif'], true);
    }

    private function tempPath(string $extension): string
    {
        $path = tempnam(WRITEPATH . 'cache', 'tpl-');
        if ($path === false) {
            throw new RuntimeException('Tidak bisa membuat temporary file');
        }

        $target = $path . '.' . $extension;
        rename($path, $target);

        return $target;
    }

    private function macroChars(string $property): string
    {
        try {
            $ref = new ReflectionProperty(TemplateProcessor::class, $property);
            $ref->setAccessible(true);

            return (string) $ref->getValue();
        } catch (Throwable) {
            return $property === 'macroOpeningChars' ? '${' : '}';
        }
    }

    private function setMacroChars(string $opening, string $closing): void
    {
        foreach (['macroOpeningChars' => $opening, 'macroClosingChars' => $closing] as $property => $value) {
            $ref = new ReflectionProperty(TemplateProcessor::class, $property);
            $ref->setAccessible(true);
            $ref->setValue(null, $value);
        }
    }
}
