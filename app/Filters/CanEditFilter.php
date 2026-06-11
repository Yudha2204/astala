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
        $subUser = $user['sub_user'] ?? null;

        $canEdit = $role === 'admin'
            || (in_array($role, ['manager', 'karyawan'], true) && $subUser === 'editor');

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
