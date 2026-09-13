<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->has('user')) {
            $user = session('user');
            if (empty($user['role']) || ! in_array($user['role'], ['admin', 'pj_gudang', 'karyawan'], true)) {
                $freshUser = (new \App\Models\UserModel())->find($user['id'] ?? 0);
                if ($freshUser && ! empty($freshUser['role'])) {
                    $user['role'] = $freshUser['role'];
                    $user['sub_user'] = $freshUser['sub_user'];
                    session()->set('user', $user);
                }
            }

            return null;
        }

        session()->setFlashdata('error', 'Silakan login terlebih dahulu');

        return redirect()->to('/auth/login');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }
}
