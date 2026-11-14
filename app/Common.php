<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (!function_exists('roundPosTotal')) {
    /**
     * Logika Pembulatan Otomatis Hasil Perhitungan Akhir (Kelipatan 500)
     * - Angka < 1000:
     *     - Jika tepat 500 => tetap 500
     *     - Selain 500 (misal 1 - 499 atau 501 - 999) => dinaikkan ke 1000
     * - Angka >= 1000:
     *     - Sisa bagi 500 (remainder) <= 250 => dibulatkan turun ke bawah (base)
     *     - Sisa bagi 500 (remainder) > 250  => dibulatkan naik ke atas (base + 500)
     * Contoh:
     *   1001 - 1250 => 1000
     *   1251 - 1500 => 1500
     *   1501 - 1750 => 1500
     *   1751 - 2000 => 2000
     *   113.999     => 114.000
     */
    function roundPosTotal($amount) {
        $amount = (float)$amount;
        if ($amount <= 0) return 0.0;
        if ($amount < 1000) {
            if (round($amount) == 500) return 500.0;
            return 1000.0;
        }
        $base = floor($amount / 500) * 500;
        $remainder = $amount - $base;
        if ($remainder > 0 && $remainder <= 250) {
            return (float)$base;
        } elseif ($remainder > 250) {
            return (float)($base + 500);
        }
        return (float)$amount;
    }
}

