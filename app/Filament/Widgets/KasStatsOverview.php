<?php

namespace App\Filament\Widgets;

use App\Enums\DuesStatus;
use App\Enums\TransactionType;
use App\Models\Dues;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KasStatsOverview extends StatsOverviewWidget
{
    protected ?string $heading = 'Ringkasan Keuangan';

    protected function getStats(): array
    {
        $income = (int) Transaction::where('type', TransactionType::Income)->sum('amount');
        $expense = (int) Transaction::where('type', TransactionType::Expense)->sum('amount');
        $balance = $income - $expense;

        // Tunggakan = tagihan yang belum lunas.
        $arrears = Dues::whereIn('status', [
            DuesStatus::Unpaid,
            DuesStatus::Partial,
            DuesStatus::Overdue,
        ]);
        $arrearsCount = (clone $arrears)->count();
        $arrearsAmount = (int) (clone $arrears)->sum('amount');

        return [
            Stat::make('Saldo Kas', $this->rupiah($balance))
                ->description('Pemasukan − Pengeluaran')
                ->color($balance >= 0 ? 'success' : 'danger'),
            Stat::make('Total Pemasukan', $this->rupiah($income))
                ->color('success'),
            Stat::make('Total Pengeluaran', $this->rupiah($expense))
                ->color('danger'),
            Stat::make('Tunggakan Iuran', $arrearsCount . ' tagihan')
                ->description($this->rupiah($arrearsAmount) . ' belum terbayar')
                ->color('warning'),
        ];
    }

    private function rupiah(int $value): string
    {
        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
