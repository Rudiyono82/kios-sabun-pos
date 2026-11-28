<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

class WaGateway
{
    /**
     * Kirim pesan WhatsApp otomatis tanpa interaksi kasir
     * 
     * @param string $phone Nomor WhatsApp tujuan
     * @param string $message Teks pesan / struk elektronik
     * @return array [success => bool, message => string, response => mixed]
     */
    public static function send($phone, $message)
    {
        // 1. Bersihkan nomor telepon dan jadikan format internasional Indonesia (62xxx)
        $phoneClean = preg_replace('/[^0-9]/', '', (string)$phone);
        if (empty($phoneClean) || strlen($phoneClean) < 8) {
            return [
                'success' => false,
                'configured' => true,
                'message' => 'Nomor WhatsApp member tidak valid atau kosong (' . $phone . ').'
            ];
        }

        if (str_starts_with($phoneClean, '0')) {
            $phoneClean = '62' . substr($phoneClean, 1);
        } elseif (str_starts_with($phoneClean, '8')) {
            $phoneClean = '62' . $phoneClean;
        }

        // 2. Ambil token & provider dari Environment Variable (.env / Railway) atau Database (profil_toko)
        $token = getenv('WA_GATEWAY_TOKEN') ?: '';
        $provider = getenv('WA_GATEWAY_PROVIDER') ?: '';

        if (empty($token)) {
            try {
                $db = \Config\Database::connect();
                // 1. Cek dari cabang pusat
                $cabang = $db->table('cabang')->where('is_pusat', 1)->get()->getRowArray();
                if (!empty($cabang['wa_gateway_token'])) {
                    $token = trim($cabang['wa_gateway_token']);
                    $provider = !empty($cabang['wa_gateway_provider']) ? trim($cabang['wa_gateway_provider']) : 'fonnte';
                }
                // 2. Cek dari profil_toko jika cabang belum diset
                if (empty($token)) {
                    $profil = $db->table('profil_toko')->get()->getRowArray();
                    if (!empty($profil['wa_gateway_token'])) {
                        $token = trim($profil['wa_gateway_token']);
                        $provider = !empty($profil['wa_gateway_provider']) ? trim($profil['wa_gateway_provider']) : 'fonnte';
                    }
                }
            } catch (\Throwable $e) {}
        }

        if (empty($provider)) {
            $provider = 'fonnte';
        }

        // Jika token belum diset, return keterangan agar kasir/owner tahu
        if (empty($token)) {
            return [
                'success' => false,
                'configured' => false,
                'message' => 'Token WhatsApp Gateway belum diset. Masukkan token di .env atau tabel profil_toko.'
            ];
        }

        // 3. Eksekusi pengiriman otomatis berdasarkan provider
        try {
            $provider = strtolower($provider);
            $resRaw = null;
            $httpCode = 0;

            if ($provider === 'wablas') {
                $url = getenv('WA_GATEWAY_URL') ?: 'https://phone.wablas.com/api/send-message';
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => http_build_query([
                        'phone'   => $phoneClean,
                        'message' => $message,
                    ]),
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: ' . $token,
                        'Content-Type: application/x-www-form-urlencoded'
                    ],
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $resRaw = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            } else {
                // Default: Fonnte (Paling stabil, banyak dipakai UMKM Indonesia & mudah)
                $url = getenv('WA_GATEWAY_URL') ?: 'https://api.fonnte.com/send';
                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST           => true,
                    CURLOPT_POSTFIELDS     => [
                        'target'      => $phoneClean,
                        'message'     => $message,
                        'countryCode' => '62',
                    ],
                    CURLOPT_HTTPHEADER     => [
                        'Authorization: ' . $token
                    ],
                    CURLOPT_TIMEOUT        => 8,
                    CURLOPT_CONNECTTIMEOUT => 4,
                    CURLOPT_SSL_VERIFYPEER => false
                ]);
                $resRaw = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }

            $resJson = json_decode((string)$resRaw, true);
            $isSuccess = ($httpCode >= 200 && $httpCode < 300);

            // Log status kirim otomatis untuk audit owner
            try {
                AuditLogModel::log(
                    'KIRIM_STRUK_WA',
                    'PENJUALAN',
                    "Kirim struk WA otomatis ke {$phoneClean} (Provider: {$provider}) - Status: " . ($isSuccess ? 'SUKSES' : 'GAGAL HTTP ' . $httpCode)
                );
            } catch (\Throwable $e) {}

            return [
                'success'    => $isSuccess,
                'configured' => true,
                'http_code'  => $httpCode,
                'message'    => $isSuccess ? 'Struk berhasil dikirim ke WhatsApp member.' : 'Gateway merespons HTTP ' . $httpCode,
                'response'   => $resJson ?: $resRaw
            ];
        } catch (\Throwable $th) {
            return [
                'success'    => false,
                'configured' => true,
                'message'    => 'Error koneksi WhatsApp Gateway: ' . $th->getMessage()
            ];
        }
    }
}
