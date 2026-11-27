<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function testSimpanEkonomi()
    {
        // Simulate logging in as KASIR1 (id_cabang = 1)
        session()->set([
            'id_user' => 1,
            'kode_user' => 'KASIR1',
            'nama_user' => 'Kasir Utama',
            'role' => 'kasir',
            'id_cabang' => 1,
            'isLoggedIn' => true
        ]);

        $pos = new \App\Controllers\Pos();
        $pos->initController(
            \Config\Services::request(),
            \Config\Services::response(),
            \Config\Services::logger()
        );

        // Prepare request body
        $payload = [
            'items' => [
                [
                    'id_barang' => 'B2020438',
                    'qty' => 1,
                    'harga_jual' => 10000,
                    'subtotal' => 10000,
                    'profit' => 3500
                ]
            ],
            'diskon' => 0,
            'potongan_tukar_jerigen' => 0,
            'kode_voucher' => null,
            'metode_bayar' => 'Cash',
            'no_kartu' => null,
            'bayar' => 10000,
            'kode_member' => null,
            'catatan' => 'Test POS simulation',
            'client_token' => 'TEST-' . time()
        ];

        // Set JSON body into request
        $request = \Config\Services::request();
        $reflection = new \ReflectionClass($request);
        $property = $reflection->getProperty('body');
        $property->setAccessible(true);
        $property->setValue($request, json_encode($payload));

        try {
            $response = $pos->simpanTransaksi();
            return $response;
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'exception',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
