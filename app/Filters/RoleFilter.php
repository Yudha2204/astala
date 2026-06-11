<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $user = session('user');

        if (! $user) {
            session()->setFlashdata('error', 'Silakan login terlebih dahulu');

            return redirect()->to('/auth/login');
        }

        if ($arguments && ! in_array($user['role'] ?? '', $arguments, true)) {
            session()->setFlashdata('error', 'Anda tidak memiliki akses ke halaman ini');

            return redirect()->to('/dashboard');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
