<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Inisialisasi Session Cabang jika belum ada
        $session = session();
        if ($session->get('is_logged_in')) {
            if (!$session->has('id_cabang')) {
                $session->set('id_cabang', 1);
            }
            if (!$session->has('nama_cabang')) {
                $db = \Config\Database::connect();
                $cab = $db->table('cabang')->where('id', $session->get('id_cabang'))->get()->getRowArray();
                $session->set('nama_cabang', $cab ? $cab['nama_cabang'] : 'KSP Cileungsi (Pusat)');
                $session->set('kode_cabang', $cab ? $cab['kode_cabang'] : 'CBG-01');
            }
        }
    }

    /**
     * Verifikasi otorisasi password Super Admin / Supervisor
     */
    protected function verifyAdminPassword(?string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        $db = \Config\Database::connect();
        $admins = $db->table('users')
            ->where('role', 'admin')
            ->where('is_active', 1)
            ->get()->getResultArray();

        foreach ($admins as $adm) {
            if (password_verify($password, $adm['password'])) {
                return true;
            }
        }

        // Fallback default password untuk dev/test
        if ($password === 'admin123' || $password === 'Admin01' || $password === '123456') {
            return true;
        }

        return false;
    }
}
