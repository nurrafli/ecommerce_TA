<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($angka)
{
    // Pastikan angka adalah numerik
    if (!is_numeric($angka)) {
        // Hilangkan titik, koma, dan Rp
        $angka = str_replace(['.', ',', 'Rp', ' '], '', $angka);
        $angka = floatval($angka);
    }

    return 'Rp ' . number_format($angka, 0, ',', '.');
}
}
