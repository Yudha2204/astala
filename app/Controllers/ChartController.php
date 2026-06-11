<?php

namespace App\Controllers;

use DateInterval;
use DateTimeImmutable;

class ChartController extends BaseController
{
    public function getLoanStats()
    {
        $period = $this->normalizePeriod((string) ($this->request->getGet('period') ?? 'daily'));
        $db = db_connect();

        [$minDate, $maxDate] = $this->dateWindow('peminjaman', 'tanggal_pinjam', $period);
        $rows = $this->queryStats($db, 'peminjaman', 'tanggal_pinjam', $period);

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->fillGaps($rows, $period, $minDate, $maxDate),
            'period' => $period,
        ]);
    }

    public function getPengambilanStats()
    {
        $period = $this->normalizePeriod((string) ($this->request->getGet('period') ?? 'daily'));
        $status = (string) ($this->request->getGet('status') ?? 'all');
        $where = [];

        if (in_array($status, ['done', 'rejected'], true)) {
            $where['status'] = $status;
        } else {
            $status = 'all';
        }

        $db = db_connect();
        [$minDate, $maxDate] = $this->dateWindow('pengambilan_aset', 'tanggal_request', $period, $where);
        $rows = $this->queryStats($db, 'pengambilan_aset', 'tanggal_request', $period, $where);
        $totalBuilder = $db->table('pengambilan_aset');
        if ($where) {
            $totalBuilder->where($where);
        }
        $total = $totalBuilder->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->fillGaps($rows, $period, $minDate, $maxDate),
            'period' => $period,
            'total' => $total,
            'status' => $status,
        ]);
    }

    private function normalizePeriod(string $period): string
    {
        return in_array($period, ['daily', 'monthly', 'yearly'], true) ? $period : 'daily';
    }

    private function dateWindow(string $table, string $column, string $period, array $where = []): array
    {
        $builder = db_connect()->table($table)
            ->select("MIN({$column}) AS min_date, MAX({$column}) AS max_date");

        if ($where) {
            $builder->where($where);
        }

        $range = $builder->get()->getRowArray() ?: [];
        $now = new DateTimeImmutable('now');
        $minDate = $range['min_date'] ? new DateTimeImmutable($range['min_date']) : $now;
        $maxDate = $range['max_date'] ? new DateTimeImmutable($range['max_date']) : $now;

        $history = match ($period) {
            'monthly' => $now->sub(new DateInterval('P12M')),
            'yearly' => $now->sub(new DateInterval('P5Y')),
            default => $now->sub(new DateInterval('P30D')),
        };

        if ($history < $minDate) {
            $minDate = $history;
        }

        if ($now > $maxDate) {
            $maxDate = $now;
        }

        return [$minDate, $maxDate];
    }

    private function queryStats($db, string $table, string $column, string $period, array $where = []): array
    {
        $dateExpression = match ($period) {
            'monthly' => "DATE_FORMAT({$column}, '%Y-%m-01')",
            'yearly' => "DATE_FORMAT({$column}, '%Y-01-01')",
            default => "DATE({$column})",
        };

        $builder = $db->table($table)
            ->select("{$dateExpression} AS date, COUNT(id) AS count", false)
            ->where("{$column} IS NOT NULL", null, false)
            ->groupBy($dateExpression, false)
            ->orderBy($dateExpression, 'ASC', false);

        if ($where) {
            $builder->where($where);
        }

        return $builder->get()->getResultArray();
    }

    private function fillGaps(array $rows, string $period, DateTimeImmutable $minDate, DateTimeImmutable $maxDate): array
    {
        $map = [];

        foreach ($rows as $row) {
            $map[$row['date']] = (int) $row['count'];
        }

        $data = [];
        $current = $this->normalizeDate($minDate, $period);
        $end = $this->normalizeDate($maxDate, $period);

        while ($current <= $end) {
            $key = match ($period) {
                'monthly' => $current->format('Y-m-01'),
                'yearly' => $current->format('Y-01-01'),
                default => $current->format('Y-m-d'),
            };

            $data[] = ['x' => $key, 'y' => $map[$key] ?? 0];
            $current = match ($period) {
                'monthly' => $current->add(new DateInterval('P1M')),
                'yearly' => $current->add(new DateInterval('P1Y')),
                default => $current->add(new DateInterval('P1D')),
            };
        }

        return $data;
    }

    private function normalizeDate(DateTimeImmutable $date, string $period): DateTimeImmutable
    {
        return match ($period) {
            'monthly' => $date->modify('first day of this month')->setTime(0, 0),
            'yearly' => $date->setDate((int) $date->format('Y'), 1, 1)->setTime(0, 0),
            default => $date->setTime(0, 0),
        };
    }
}
