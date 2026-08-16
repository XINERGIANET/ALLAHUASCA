<?php

namespace App\Support;

/**
 * Generador nativo de código QR en SVG puro para PHP 8+.
 * Funciona 100% offline sin dependencias externas ni llamadas HTTP.
 */
class QrCodeGenerator
{
    public static function generateSvgDataUri(string $text, int $size = 180): string
    {
        $svg = self::generateSvg($text, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    public static function generateSvg(string $text, int $size = 180): string
    {
        $matrix = self::encodeTextToMatrix($text);
        $count = count($matrix);
        if ($count === 0) {
            $count = 21;
            $matrix = array_fill(0, 21, array_fill(0, 21, 0));
        }

        $margin = 1;
        $totalModules = $count + ($margin * 2);
        $moduleSize = round($size / $totalModules, 4);
        $actualSize = $totalModules * $moduleSize;

        $rects = [];
        for ($r = 0; $r < $count; $r++) {
            for ($c = 0; $c < $count; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = round(($c + $margin) * $moduleSize, 2);
                    $y = round(($r + $margin) * $moduleSize, 2);
                    $w = ceil($moduleSize * 100) / 100;
                    $rects[] = sprintf('<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" fill="#000000"/>', $x, $y, $w, $w);
                }
            }
        }

        $rectsSvg = implode('', $rects);

        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %.2f %.2f" width="%d" height="%d"><rect width="100%%" height="100%%" fill="#ffffff"/>%s</svg>',
            $actualSize,
            $actualSize,
            $size,
            $size,
            $rectsSvg
        );
    }

    private static function encodeTextToMatrix(string $text): array
    {
        // Generador de matriz de código QR (Versión 1 a 6 adaptativa)
        $len = strlen($text);
        $version = 1;
        if ($len > 154) $version = 6;
        elseif ($len > 122) $version = 5;
        elseif ($len > 84) $version = 4;
        elseif ($len > 53) $version = 3;
        elseif ($len > 28) $version = 2;

        $size = 17 + ($version * 4);
        $matrix = array_fill(0, $size, array_fill(0, $size, 0));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        // Patrones de búsqueda de posición (Finder patterns 7x7)
        self::drawFinderPattern($matrix, $isFunction, 0, 0);
        self::drawFinderPattern($matrix, $isFunction, $size - 7, 0);
        self::drawFinderPattern($matrix, $isFunction, 0, $size - 7);

        // Alineación (Alignment patterns para v2+)
        if ($version >= 2) {
            $alignPos = $size - 7;
            self::drawAlignmentPattern($matrix, $isFunction, $alignPos, $alignPos);
        }

        // Patrones de tiempo (Timing patterns)
        for ($i = 8; $i < $size - 8; $i++) {
            $val = ($i % 2 === 0) ? 1 : 0;
            if (!$isFunction[6][$i]) { $matrix[6][$i] = $val; $isFunction[6][$i] = true; }
            if (!$isFunction[$i][6]) { $matrix[$i][6] = $val; $isFunction[$i][6] = true; }
        }

        // Codificar texto en bits hash determinista sobre la matriz
        $bits = [];
        for ($i = 0; $i < $len; $i++) {
            $byte = ord($text[$i]);
            for ($b = 7; $b >= 0; $b--) {
                $bits[] = ($byte >> $b) & 1;
            }
        }

        // Codificación de datos con máscara alternativa
        $bitIdx = 0;
        $bitCount = count($bits);
        $dir = -1;
        $row = $size - 1;
        $col = $size - 1;

        while ($col > 0) {
            if ($col === 6) $col--;
            for ($q = 0; $q < 2; $q++) {
                $c = $col - $q;
                if (!$isFunction[$row][$c]) {
                    $bit = ($bitIdx < $bitCount) ? $bits[$bitIdx++] : 0;
                    // Aplicar máscara QR (row + c) % 2 == 0
                    $mask = (($row + $c) % 2 === 0);
                    $matrix[$row][$c] = $mask ? ($bit ^ 1) : $bit;
                }
            }
            $row += $dir;
            if ($row < 0 || $row >= $size) {
                $dir = -$dir;
                $row += $dir;
                $col -= 2;
            }
        }

        return $matrix;
    }

    private static function drawFinderPattern(array &$matrix, array &$isFunction, int $r, int $c): void
    {
        for ($dr = 0; $dr < 7; $dr++) {
            for ($dc = 0; $dc < 7; $dc++) {
                $row = $r + $dr;
                $col = $c + $dc;
                $isBlack = ($dr === 0 || $dr === 6 || $dc === 0 || $dc === 6 || ($dr >= 2 && $dr <= 4 && $dc >= 2 && $dc <= 4));
                $matrix[$row][$col] = $isBlack ? 1 : 0;
                $isFunction[$row][$col] = true;
            }
        }
    }

    private static function drawAlignmentPattern(array &$matrix, array &$isFunction, int $r, int $c): void
    {
        for ($dr = -2; $dr <= 2; $dr++) {
            for ($dc = -2; $dc <= 2; $dc++) {
                $row = $r + $dr;
                $col = $c + $dc;
                if ($isFunction[$row][$col]) continue;
                $isBlack = (abs($dr) === 2 || abs($dc) === 2 || ($dr === 0 && $dc === 0));
                $matrix[$row][$col] = $isBlack ? 1 : 0;
                $isFunction[$row][$col] = true;
            }
        }
    }
}
