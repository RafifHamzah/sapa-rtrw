<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class IncomeExpenseChart extends ChartWidget
{
    protected ?string $heading = 'Pemasukan vs Pengeluaran (6 Bulan Terakhir)';

    protected function getData(): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        // Iterasi per bulan di PHP agar tidak bergantung fungsi tanggal spesifik DB.
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $labels[] = $month->translatedFormat('M Y');

            $income[] = (int) Transaction::where('type', TransactionType::Income)
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');

            $expense[] = (int) Transaction::where('type', TransactionType::Expense)
                ->whereBetween('transaction_date', [$start, $end])
                ->sum('amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pemasukan',
                    'data' => $income,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.5)',
                    'borderColor' => 'rgb(16, 185, 129)',
                ],
                [
                    'label' => 'Pengeluaran',
                    'data' => $expense,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.5)',
                    'borderColor' => 'rgb(239, 68, 68)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
