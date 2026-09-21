<?php

namespace App\Services;

use App\Models\QualityReport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class QualityOcrService
{
    /**
     * Guarda la foto del ticket y, si Tesseract está disponible, intenta
     * reconocer los parámetros del LACTOMAT en el texto extraído.
     *
     * @return array{path: string, fields: array<string, float>, text: ?string, ocr_available: bool}
     */
    public function scan(UploadedFile $photo): array
    {
        $path = $photo->store('quality-tickets', 'public');
        $fullPath = Storage::disk('public')->path($path);

        $text = null;
        $ocrAvailable = true;

        try {
            $text = (new TesseractOCR($fullPath))->lang('spa', 'eng')->run();
        } catch (\Throwable $e) {
            $ocrAvailable = false;
            Log::warning('OCR de ticket LACTOMAT no disponible: '.$e->getMessage());
        }

        return [
            'path' => $path,
            'fields' => $text ? $this->extractFields($text) : [],
            'text' => $text,
            'ocr_available' => $ocrAvailable,
        ];
    }

    /**
     * @return array<string, float>
     */
    protected function extractFields(string $text): array
    {
        $patterns = [
            'grasa_pct' => '/grasa[^\d\-]*(-?\d+[.,]?\d*)/i',
            'proteina_pct' => '/prote[ií]na[^\d\-]*(-?\d+[.,]?\d*)/i',
            'lactosa_pct' => '/lactosa[^\d\-]*(-?\d+[.,]?\d*)/i',
            'solidos_no_grasos_pct' => '/s(?:o|ó)lidos\s*no\s*grasos|SNG[^\d\-]*(-?\d+[.,]?\d*)/i',
            'total_solidos_pct' => '/total\s*s(?:o|ó)lidos|s(?:o|ó)lidos\s*totales[^\d\-]*(-?\d+[.,]?\d*)/i',
            'agua_aniadida_pct' => '/agua\s*a(?:ñ|n)adida[^\d\-]*(-?\d+[.,]?\d*)/i',
            'ph' => '/\bpH[^\d\-]*(-?\d+[.,]?\d*)/i',
            'punto_congelacion' => '/punto\s*de\s*congelaci[oó]n[^\d\-]*(-?\d+[.,]?\d*)/i',
            'densidad' => '/densidad[^\d\-]*(-?\d+[.,]?\d*)/i',
            'temperatura' => '/temperatura[^\d\-]*(-?\d+[.,]?\d*)/i',
        ];

        $fields = [];
        foreach ($patterns as $field => $pattern) {
            if (preg_match($pattern, $text, $matches)) {
                $fields[$field] = (float) str_replace(',', '.', $matches[1]);
            }
        }

        return array_intersect_key($fields, array_flip([...array_keys(QualityReport::QUALITY_PARAMS), 'densidad', 'temperatura']));
    }
}
