<?php

namespace App\Controllers;

class MigrationStubController extends BaseController
{
    public function index()
    {
        return view('migration_stub', [
            'title' => 'ASTALA CI4 Migration',
            'path' => $this->request->getUri()->getPath(),
        ]);
    }

    public function api()
    {
        return $this->response->setJSON([
            'status' => 'migration-pending',
            'path' => $this->request->getUri()->getPath(),
        ]);
    }
}
