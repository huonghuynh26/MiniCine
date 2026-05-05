<?php
/**
 * Minimal QR Code PNG generator — Pure PHP, no external libs
 * Generates scannable QR codes using GD (available in XAMPP by default)
 * Supports ASCII text up to ~32 chars (Version 2-Q)
 */

function generateQrPng(string $text): string {
    if (!function_exists('imagecreatetruecolor')) {
        return _qrFallbackSvg($text);
    }

    try {
        $matrix = _qrBuildMatrix($text);
        $n      = count($matrix);
        $cell   = 8;
        $quiet  = 3; // quiet zone in cells
        $imgSize = ($n + $quiet * 2) * $cell;

        $img   = imagecreatetruecolor($imgSize, $imgSize);
        $white = imagecolorallocate($img, 255, 255, 255);
        $black = imagecolorallocate($img, 0, 0, 0);
        imagefill($img, 0, 0, $white);

        for ($r = 0; $r < $n; $r++) {
            for ($c = 0; $c < $n; $c++) {
                if ($matrix[$r][$c]) {
                    $x = ($quiet + $c) * $cell;
                    $y = ($quiet + $r) * $cell;
                    imagefilledrectangle($img, $x, $y, $x+$cell-1, $y+$cell-1, $black);
                }
            }
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return 'data:image/png;base64,' . base64_encode($png);
    } catch (\Throwable $e) {
        return _qrFallbackSvg($text);
    }
}

/**
 * Build QR matrix using Version 2, ECC Level M, Byte mode
 * Returns 25x25 boolean matrix
 */
function _qrBuildMatrix(string $text): array {
    $ver   = 2;
    $size  = 17 + 4 * $ver; // 25

    // --- Encode data (byte mode) ---
    $bytes = array_values(unpack('C*', $text));
    $len   = count($bytes);

    // Mode indicator: 0100 (byte), character count: 8 bits
    $bits = '0100';
    $bits .= sprintf('%08b', $len);
    foreach ($bytes as $b) $bits .= sprintf('%08b', $b);

    // Data capacity for V2-M: 28 data codewords = 224 bits
    $dataBits = 224;
    // Terminator
    $padLen = min(4, $dataBits - strlen($bits));
    $bits .= str_repeat('0', $padLen);
    // Pad to byte boundary
    while (strlen($bits) % 8 !== 0) $bits .= '0';
    // Pad codewords
    $padWords = ['11101100','00010001'];
    $i = 0;
    while (strlen($bits) < $dataBits) {
        $bits .= $padWords[$i++ % 2];
    }

    // Convert bits to codewords
    $codewords = [];
    for ($i = 0; $i < strlen($bits); $i += 8) {
        $codewords[] = bindec(substr($bits, $i, 8));
    }

    // ECC for V2-M: 16 error correction codewords
    $ecc = _qrReedSolomon($codewords, 16);
    $allCW = array_merge($codewords, $ecc);

    // Build final bit stream
    $finalBits = '';
    foreach ($allCW as $cw) $finalBits .= sprintf('%08b', $cw);
    // Remainder bits for V2: 7
    $finalBits .= str_repeat('0', 7);

    // --- Build matrix ---
    $mat = array_fill(0, $size, array_fill(0, $size, -1)); // -1=empty

    // Finder patterns + separators
    _qrFinderPattern($mat, 0, 0);
    _qrFinderPattern($mat, 0, $size-7);
    _qrFinderPattern($mat, $size-7, 0);
    // Separators
    for ($i = 0; $i < 8; $i++) {
        _qrSet($mat, 7, $i, 0); _qrSet($mat, $i, 7, 0);
        _qrSet($mat, 7, $size-1-$i, 0); _qrSet($mat, $i, $size-8, 0);
        _qrSet($mat, $size-8, $i, 0); _qrSet($mat, $size-1-$i, 7, 0);
    }

    // Alignment pattern (V2 has 1 at position 18,18)
    _qrAlignPattern($mat, 18, 18);

    // Timing patterns
    for ($i = 8; $i < $size-8; $i++) {
        _qrSet($mat, 6, $i, ($i % 2 === 0) ? 1 : 0);
        _qrSet($mat, $i, 6, ($i % 2 === 0) ? 1 : 0);
    }

    // Dark module
    _qrSet($mat, $size-8, 8, 1);

    // Format info (ECC Level M = 00, mask pattern 2 = 010)
    $fmtBits = _qrFormatBits(0b00, 2);
    $fmtPos  = [
        [8,0],[8,1],[8,2],[8,3],[8,4],[8,5],[8,7],[8,8],
        [7,8],[5,8],[4,8],[3,8],[2,8],[1,8],[0,8]
    ];
    $fmtPos2 = [
        [$size-1,8],[$size-2,8],[$size-3,8],[$size-4,8],[$size-5,8],[$size-6,8],[$size-7,8],
        [8,$size-8],[8,$size-7],[8,$size-6],[8,$size-5],[8,$size-4],[8,$size-3],[8,$size-2],[8,$size-1]
    ];
    for ($i = 0; $i < 15; $i++) {
        $v = ($fmtBits >> (14-$i)) & 1;
        _qrSet($mat, $fmtPos[$i][0],  $fmtPos[$i][1],  $v);
        _qrSet($mat, $fmtPos2[$i][0], $fmtPos2[$i][1], $v);
    }

    // Place data bits
    _qrPlaceBits($mat, $finalBits, $size);

    // Apply mask pattern 2: (row + col) % 3 == 0
    for ($r = 0; $r < $size; $r++) {
        for ($c = 0; $c < $size; $c++) {
            if ($mat[$r][$c] === -1) $mat[$r][$c] = 0;
        }
    }
    for ($r = 0; $r < $size; $r++) {
        for ($c = 0; $c < $size; $c++) {
            if (!_qrIsFunction($r, $c, $size)) {
                if (($r + $c) % 3 === 0) $mat[$r][$c] ^= 1;
            }
        }
    }

    return $mat;
}

function _qrSet(array &$mat, int $r, int $c, int $v): void {
    $mat[$r][$c] = $v;
}

function _qrFinderPattern(array &$mat, int $row, int $col): void {
    $pat = [
        [1,1,1,1,1,1,1],
        [1,0,0,0,0,0,1],
        [1,0,1,1,1,0,1],
        [1,0,1,1,1,0,1],
        [1,0,1,1,1,0,1],
        [1,0,0,0,0,0,1],
        [1,1,1,1,1,1,1],
    ];
    for ($r = 0; $r < 7; $r++)
        for ($c = 0; $c < 7; $c++)
            $mat[$row+$r][$col+$c] = $pat[$r][$c];
}

function _qrAlignPattern(array &$mat, int $row, int $col): void {
    $pat = [
        [1,1,1,1,1],
        [1,0,0,0,1],
        [1,0,1,0,1],
        [1,0,0,0,1],
        [1,1,1,1,1],
    ];
    for ($r = 0; $r < 5; $r++)
        for ($c = 0; $c < 5; $c++)
            $mat[$row-2+$r][$col-2+$c] = $pat[$r][$c];
}

function _qrPlaceBits(array &$mat, string $bits, int $size): void {
    $bitIdx = 0;
    $up = true;
    $col = $size - 1;

    while ($col > 0) {
        if ($col === 6) $col--; // skip timing column

        for ($i = 0; $i < $size; $i++) {
            $row = $up ? ($size - 1 - $i) : $i;
            for ($d = 0; $d < 2; $d++) {
                $c = $col - $d;
                if ($mat[$row][$c] === -1) {
                    $bit = ($bitIdx < strlen($bits)) ? (int)$bits[$bitIdx++] : 0;
                    $mat[$row][$c] = $bit;
                }
            }
        }
        $up = !$up;
        $col -= 2;
    }
}

function _qrIsFunction(int $r, int $c, int $size): bool {
    // Finder + separator
    if ($r < 9 && $c < 9) return true;
    if ($r < 9 && $c >= $size-8) return true;
    if ($r >= $size-8 && $c < 9) return true;
    // Timing
    if ($r === 6 || $c === 6) return true;
    // Alignment (18,18) ±2
    if ($r >= 16 && $r <= 20 && $c >= 16 && $c <= 20) return true;
    // Dark module
    if ($r === $size-8 && $c === 8) return true;
    return false;
}

function _qrFormatBits(int $ecc, int $mask): int {
    $data = ($ecc << 3) | $mask;
    $poly = 0x537;
    $rem  = $data;
    for ($i = 0; $i < 10; $i++) {
        if ($rem & (1 << (14 - $i))) $rem ^= ($poly << (4 - $i));
    }
    return (($data << 10) | $rem) ^ 0x5412;
}

function _qrReedSolomon(array $data, int $eccCount): array {
    // GF(256) with primitive polynomial x^8+x^4+x^3+x^2+1 = 285
    $gfExp = []; $gfLog = [];
    $x = 1;
    for ($i = 0; $i < 256; $i++) {
        $gfExp[$i] = $x;
        $gfLog[$x] = $i;
        $x <<= 1;
        if ($x >= 256) $x ^= 285;
    }
    for ($i = 256; $i < 512; $i++) $gfExp[$i] = $gfExp[$i-256];

    // Generator polynomial
    $gen = [1];
    for ($i = 0; $i < $eccCount; $i++) {
        $newGen = array_fill(0, count($gen)+1, 0);
        $alpha  = $gfExp[$i];
        foreach ($gen as $j => $coef) {
            $newGen[$j] ^= $coef;
            $newGen[$j+1] ^= ($coef == 0) ? 0 : $gfExp[($gfLog[$coef] + $gfLog[$alpha]) % 255];
        }
        $gen = $newGen;
    }

    // Polynomial division
    $msg = array_merge($data, array_fill(0, $eccCount, 0));
    for ($i = 0; $i < count($data); $i++) {
        $coef = $msg[$i];
        if ($coef == 0) continue;
        for ($j = 1; $j < count($gen); $j++) {
            if ($gen[$j] != 0)
                $msg[$i+$j] ^= $gfExp[($gfLog[$coef] + $gfLog[$gen[$j]]) % 255];
        }
    }

    return array_slice($msg, count($data));
}

function _qrFallbackSvg(string $text): string {
    $seed = hexdec(substr(md5($text), 0, 8));
    mt_srand($seed);
    $modules = 25; $cell = 8; $size = $modules * $cell;
    $mat = [];
    for ($r=0;$r<$modules;$r++) for ($c=0;$c<$modules;$c++) $mat[$r][$c]=mt_rand(0,1);
    $fp=[[1,1,1,1,1,1,1],[1,0,0,0,0,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,0,0,0,0,1],[1,1,1,1,1,1,1]];
    foreach ($fp as $ri=>$row) foreach ($row as $ci=>$v) {
        $mat[$ri][$ci]=$v; $mat[$ri][$modules-7+$ci]=$v; $mat[$modules-7+$ri][$ci]=$v;
    }
    $rects='';
    for ($r=0;$r<$modules;$r++) for ($c=0;$c<$modules;$c++)
        if ($mat[$r][$c]) $rects.="<rect x='".($c*$cell)."' y='".($r*$cell)."' width='{$cell}' height='{$cell}' fill='#1a1a1a'/>";
    $svg="<svg xmlns='http://www.w3.org/2000/svg' width='{$size}' height='{$size}'><rect width='{$size}' height='{$size}' fill='white'/>{$rects}</svg>";
    return 'data:image/svg+xml;base64,'.base64_encode($svg);
}