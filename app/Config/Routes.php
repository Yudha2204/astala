<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', static function () {
    return session()->has('user')
        ? redirect()->to('/dashboard')
        : redirect()->to('/auth/login');
});

$routes->group('auth', ['filter' => 'guest'], static function (RouteCollection $routes) {
    $routes->get('login', 'AuthController::showLogin');
    $routes->post('login', 'AuthController::login');
    $routes->get('signup', 'AuthController::showSignup');
    $routes->post('signup', 'AuthController::signup');
});

$routes->get('auth/logout', 'AuthController::logout', ['filter' => 'auth']);
$routes->get('auth/sso', 'AuthController::sso');

$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'DashboardController::index');
    $routes->get('dashboard/api/chart/loans', 'ChartController::getLoanStats');
    $routes->get('dashboard/api/chart/pengambilan', 'ChartController::getPengambilanStats');

    $routes->group('barang', ['filter' => 'role:admin,manager,karyawan'], static function (RouteCollection $routes) {
        $routes->get('/', 'BarangController::index');
        $routes->get('detail/(:num)', 'BarangController::detail/$1');
        $routes->get('api/barcode/(:segment)', 'BarangController::getByBarcode/$1');
        $routes->get('api/names', 'BarangController::getNames');
        $routes->get('add', 'BarangController::showAdd', ['filter' => 'canEdit']);
        $routes->post('add', 'BarangController::add', ['filter' => 'canEdit']);
        $routes->get('edit/(:num)', 'BarangController::showEdit/$1', ['filter' => 'canEdit']);
        $routes->post('edit/(:num)', 'BarangController::edit/$1', ['filter' => 'canEdit']);
        $routes->post('delete/(:num)', 'BarangController::delete/$1', ['filter' => 'canEdit']);
        $routes->post('toggle-qr/(:num)', 'BarangController::toggleQR/$1', ['filter' => 'canEdit']);
    });

    $routes->group('peminjaman', ['filter' => 'role:admin,manager,karyawan'], static function (RouteCollection $routes) {
        $routes->get('items', 'PeminjamanController::items');
        $routes->get('form', 'PeminjamanController::form');
        $routes->post('borrow', 'PeminjamanController::borrow');
        $routes->get('current', 'PeminjamanController::current');
        $routes->get('return/(:num)', 'PeminjamanController::returnForm/$1');
        $routes->post('return/(:num)', 'PeminjamanController::returnItem/$1');
        $routes->get('history', 'PeminjamanController::history');
        $routes->get('detail/(:num)', 'PeminjamanController::detail/$1');
    });

    $routes->group('pengambilan', static function (RouteCollection $routes) {
        $routes->get('/', 'PengambilanController::index', ['filter' => 'role:mitra']);
        $routes->get('request', 'PengambilanController::showRequest', ['filter' => 'role:mitra']);
        $routes->post('request', 'PengambilanController::submitRequest', ['filter' => 'role:mitra']);
        $routes->get('detail/(:num)', 'PengambilanController::detail/$1');
        $routes->get('pickup/(:num)', 'PengambilanController::showPickup/$1', ['filter' => 'role:mitra']);
        $routes->post('pickup/(:num)', 'PengambilanController::submitPickup/$1', ['filter' => 'role:mitra']);
        $routes->get('download/(:num)', 'PengambilanController::download/$1');

        $routes->get('admin', 'PengambilanController::adminIndex', ['filter' => 'canViewInventory']);
        $routes->get('admin/detail/(:num)', 'PengambilanController::adminDetail/$1', ['filter' => 'canViewInventory']);
        $routes->post('admin/approve/(:num)', 'PengambilanController::approve/$1', ['filter' => 'canEdit']);
        $routes->post('admin/reject/(:num)', 'PengambilanController::reject/$1', ['filter' => 'canEdit']);
        $routes->get('admin/confirm/(:num)', 'PengambilanController::showConfirm/$1', ['filter' => 'canEdit']);
        $routes->post('admin/confirm/(:num)', 'PengambilanController::submitConfirm/$1', ['filter' => 'canEdit']);
        $routes->get('admin/export/template', 'ReportController::exportPDF/pengambilan', ['filter' => 'canViewInventory']);
    });

    $routes->group('admin', static function (RouteCollection $routes) {
        $routes->get('loans', 'PeminjamanController::allLoans', ['filter' => 'canViewInventory']);
        $routes->get('logs', 'LogController::index', ['filter' => 'role:admin']);
        $routes->get('report/template/inventory', 'ReportController::exportPDF/inventory', ['filter' => 'canEdit']);
        $routes->get('report/template/loans', 'ReportController::exportPDF/my-loans', ['filter' => 'canEdit']);
        $routes->get('report/template/all-loans', 'ReportController::exportPDF/loans', ['filter' => 'canEdit']);
        $routes->get('report/template/logs', 'ReportController::exportPDF/logs', ['filter' => 'role:admin']);
        $routes->get('report/template/pengambilan', 'ReportController::exportPDF/pengambilan', ['filter' => 'canEdit']);
        $routes->get('report/pdf/(:segment)', 'ReportController::exportPDF/$1', ['filter' => 'canEdit']);
        $routes->get('report/excel/(:segment)', 'ReportController::exportExcel/$1', ['filter' => 'canEdit']);

        $routes->group('gudang', ['filter' => 'canEdit'], static function (RouteCollection $routes) {
            $routes->get('/', 'GudangController::index');
            $routes->get('add', 'GudangController::showAdd');
            $routes->post('add', 'GudangController::add');
            $routes->get('edit/(:num)', 'GudangController::showEdit/$1');
            $routes->post('edit/(:num)', 'GudangController::update/$1');
            $routes->post('delete/(:num)', 'GudangController::delete/$1');
        });

        $routes->group('aset-material', ['filter' => 'canViewInventory'], static function (RouteCollection $routes) {
            $routes->get('/', 'AsetMaterialController::index');
            $routes->get('detail/(:num)', 'AsetMaterialController::detail/$1');
            $routes->get('add', 'AsetMaterialController::showAdd', ['filter' => 'canEdit']);
            $routes->post('add', 'AsetMaterialController::add', ['filter' => 'canEdit']);
            $routes->get('add-stock/(:num)', 'AsetMaterialController::showAddStock/$1', ['filter' => 'canEdit']);
            $routes->post('add-stock/(:num)', 'AsetMaterialController::addStock/$1', ['filter' => 'canEdit']);
            $routes->get('edit/(:num)', 'AsetMaterialController::showEdit/$1', ['filter' => 'canEdit']);
            $routes->post('edit/(:num)', 'AsetMaterialController::update/$1', ['filter' => 'canEdit']);
            $routes->post('delete/(:num)', 'AsetMaterialController::delete/$1', ['filter' => 'canEdit']);
            $routes->get('export/template', 'ReportController::exportPDF/aset-material', ['filter' => 'canEdit']);
            $routes->get('api/by-gudang/(:num)', 'AsetMaterialController::getByGudang/$1');
            $routes->get('api/check-stock', 'AsetMaterialController::checkStock');
        });
    });

    $routes->group('notifications', static function (RouteCollection $routes) {
        $routes->get('/', 'NotifikasiController::index');
        $routes->get('read/(:num)', 'NotifikasiController::markAsRead/$1');
        $routes->post('mark-all-read', 'NotifikasiController::markAllAsRead');
        $routes->get('api/unread-count', 'NotifikasiController::unreadCount');
        $routes->get('api/recent', 'NotifikasiController::recent');
        $routes->post('mark-read/(:num)', 'NotifikasiController::markAsReadPost/$1');
    });

    $routes->group('profile', static function (RouteCollection $routes) {
        $routes->get('/', 'ProfileController::index');
        $routes->post('update', 'ProfileController::update');
        $routes->post('password', 'ProfileController::password');
        $routes->get('settings', 'ProfileController::settings');
        $routes->post('settings/theme', 'ProfileController::updateTheme');
    });
});
