<?php

namespace App\Filament\Widgets;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

/**
 * Distribusi pengeluaran kas per kategori (doughnut). Membantu pengurus melihat
 * ke mana dana warga dialokasikan secara sekilas.
 */
class FundDistributionChart extends ChartWidget
{
    protected ?string $heading = 'Distribusi Pengeluaran per Kategori';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $rows = Transaction::query()
            ->where('type', TransactionType::Expense)
            ->selectRaw('transaction_category_id, SUM(amount) as total')
            ->groupBy('transaction_category_id')
            ->with('category')
            ->get();

        $labels = [];
        $data = [];

        foreach ($rows as $row) {
            $labels[] = $row->category?->name ?? 'Lainnya';
            $data[] = (int) $row->total;
        }

        // Palet hijau→toska→amber→merah agar tetap selaras brand namun terbaca.
        $palette = [
            'rgb(16, 185, 129)',   // emerald
            'rgb(5, 150, 105)',    // green
            'rgb(20, 184, 166)',   // teal
            'rgb(59, 130, 246)',   // blue
            'rgb(245, 158, 11)',   // amber
            'rgb(249, 115, 22)',   // orange
            'rgb(239, 68, 68)',    // red
            'rgb(139, 92, 246)',   // violet
        ];

        return [
            'datasets' => [
                [
                    'label' => 'Pengeluaran',
                    'data' => $data,
                    'backgroundColor' => array_slice(
                        array_pad($palette, count($data), $palette),
                        0,
                        count($data),
                    ),
                    'borderWidth' => 0,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
        ];
    }
}
