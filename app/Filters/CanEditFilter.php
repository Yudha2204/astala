<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class CanEditFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');
        $role = $user['role'] ?? null;
        $canEdit = in_array($role, ['admin', 'pj_gudang'], true);

        if ($canEdit) {
            return null;
        }

        session()->setFlashdata('error', 'Anda tidak memiliki izin untuk melakukan aksi ini');

        return redirect()->to('/dashboard');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
