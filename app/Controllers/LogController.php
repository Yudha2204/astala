<?php

namespace App\Controllers;

use App\Models\ActivityLogModel;

class LogController extends BaseController
{
    private const PER_PAGE = 20;

    public function index()
    {
        $query = [
            'search' => trim((string) $this->request->getGet('search')),
            'action' => trim((string) $this->request->getGet('action')),
        ];
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));

        $countBuilder = $this->baseBuilder();
        $this->applyFilters($countBuilder, $query);
        $total = $countBuilder->countAllResults();

        $dataBuilder = $this->baseBuilder();
        $this->applyFilters($dataBuilder, $query);
        $logs = $dataBuilder
            ->orderBy('l.created_at', 'DESC')
            ->limit(self::PER_PAGE, ($page - 1) * self::PER_PAGE)
            ->get()
            ->getResultArray();

        $actions = array_column(
            (new ActivityLogModel())->select('action')->groupBy('action')->orderBy('action', 'ASC')->findAll(),
            'action'
        );

        return view('admin/logs', [
            'title' => 'Log Aktivitas - ASTALA',
            'user' => session('user'),
            'logs' => $logs,
            'actions' => $actions,
            'pagination' => $this->pagination($page, $total),
            'query' => $query,
        ]);
    }

    private function baseBuilder()
    {
        return db_connect()->table('activity_logs l')
            ->select('l.*, u.nama AS user_nama, u.email AS user_email')
            ->join('users u', 'u.id = l.user_id', 'left');
    }

    private function applyFilters($builder, array $query): void
    {
        if ($query['search'] !== '') {
            $builder->groupStart()
                ->like('l.description', $query['search'])
                ->orLike('u.nama', $query['search'])
                ->orLike('u.email', $query['search'])
                ->groupEnd();
        }

        if ($query['action'] !== '') {
            $builder->where('l.action', $query['action']);
        }
    }

    private function pagination(int $page, int $total): array
    {
        $totalPages = max(1, (int) ceil($total / self::PER_PAGE));

        return [
            'page' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'hasPrev' => $page > 1,
            'hasNext' => $page < $totalPages,
        ];
    }
}
