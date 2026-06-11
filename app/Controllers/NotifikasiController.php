<?php

namespace App\Controllers;

use App\Models\NotifikasiModel;

class NotifikasiController extends BaseController
{
    public function index()
    {
        $user = session('user');
        $notifications = (new NotifikasiModel())
            ->where('user_id', $user['id'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('notifications/index', [
            'title' => 'Notifikasi - ASTALA',
            'user' => $user,
            'notifications' => $notifications,
        ]);
    }

    public function unreadCount()
    {
        $user = session('user');
        $count = (new NotifikasiModel())
            ->where('user_id', $user['id'])
            ->where('is_read', false)
            ->countAllResults();

        return $this->response->setJSON(['count' => $count]);
    }

    public function recent()
    {
        $user = session('user');
        $notifications = (new NotifikasiModel())
            ->where('user_id', $user['id'])
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        return $this->response->setJSON($notifications);
    }

    public function markAsRead(int $id)
    {
        $user = session('user');
        db_connect()->table('notifikasi')
            ->where('user_id', $user['id'])
            ->where('id', $id)
            ->set(['is_read' => true])
            ->update();

        return redirect()->to('/notifications');
    }

    public function markAsReadPost(int $id)
    {
        $user = session('user');
        db_connect()->table('notifikasi')
            ->where('user_id', $user['id'])
            ->where('id', $id)
            ->set(['is_read' => true])
            ->update();

        return $this->response->setJSON(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = session('user');
        db_connect()->table('notifikasi')
            ->where('user_id', $user['id'])
            ->set(['is_read' => true])
            ->update();

        return $this->response->setJSON(['success' => true]);
    }
}
