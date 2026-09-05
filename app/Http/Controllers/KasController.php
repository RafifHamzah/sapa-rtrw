<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Rt;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class KasController extends Controller
{
    /**
     * Kas transparan: seluruh warga bisa melihat riwayat transaksi RT
     * (read-only) beserta ringkasan saldo & grafik bulanan.
     */
    public function index(Request $request): View
    {
        $rt = $request->user()->rt ?? $request->user()->resident?->family?->rt ?? Rt::query()->first();
        $rtId = $rt?->id;

        $type = in_array($request->query('type'), ['income', 'expense'], true)
            ? $request->query('type')
            : null;

        $transactions = Transaction::with('category')
            ->when($rtId, fn ($q) => $q->where('rt_id', $rtId))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $income = (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
            ->where('type', TransactionType::Income)->sum('amount');
        $expense = (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
            ->where('type', TransactionType::Expense)->sum('amount');

        return view('kas.index', [
            'rt' => $rt,
            'transactions' => $transactions,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'activeType' => $type,
            'chart' => $this->monthlyChart($rtId),
        ]);
    }

    /**
     * Data 6 bulan terakhir untuk grafik income vs expense.
     *
     * @return array<int, array{label: string, income: int, expense: int}>
     */
    private function monthlyChart(?int $rtId): array
    {
        $months = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $start = $month->copy()->startOfMonth();
            $end = $month->copy()->endOfMonth();

            $months[] = [
                'label' => $month->translatedFormat('M'),
                'income' => (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
                    ->where('type', TransactionType::Income)
                    ->whereBetween('transaction_date', [$start, $end])->sum('amount'),
                'expense' => (int) Transaction::when($rtId, fn ($q) => $q->where('rt_id', $rtId))
                    ->where('type', TransactionType::Expense)
                    ->whereBetween('transaction_date', [$start, $end])->sum('amount'),
            ];
        }

        return $months;
    }
}
