<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CanViewInventoryFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        $role = $user['role'] ?? null;

        if (in_array($role, ['admin', 'manager', 'karyawan'], true)) {
            return null;
        }

        session()->setFlashdata('error', 'Anda tidak memiliki izin untuk melihat halaman ini');

        return redirect()->to('/dashboard');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
