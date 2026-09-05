<?php

namespace App\Http\Controllers;

use App\Enums\DuesStatus;
use App\Enums\TransactionType;
use App\Models\Announcement;
use App\Models\Complaint;
use App\Models\Dues;
use App\Models\LetterRequest;
use App\Models\Rt;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $resident = $user->resident;
        $family = $resident?->family;
        $rt = $user->rt ?? $family?->rt ?? Rt::query()->first();

        // Saldo kas RT (transparansi publik untuk warga).
        $income = (int) Transaction::when($rt, fn ($q) => $q->where('rt_id', $rt->id))
            ->where('type', TransactionType::Income)->sum('amount');
        $expense = (int) Transaction::when($rt, fn ($q) => $q->where('rt_id', $rt->id))
            ->where('type', TransactionType::Expense)->sum('amount');

        // Status iuran keluarga saya.
        $myDues = $family
            ? Dues::where('family_id', $family->id)->latest('period_year')->latest('period_month')->get()
            : collect();
        $arrears = $myDues->whereNotIn('status', [DuesStatus::Paid]);

        return view('dashboard', [
            'user' => $user,
            'resident' => $resident,
            'rt' => $rt,
            'balance' => $income - $expense,
            'income' => $income,
            'expense' => $expense,
            'myDues' => $myDues,
            'arrearsCount' => $arrears->count(),
            'arrearsTotal' => (int) $arrears->sum('amount'),
            'announcements' => Announcement::with('author')
                ->when($rt, fn ($q) => $q->where('rt_id', $rt->id))
                ->published()->take(3)->get(),
            'letters' => $resident
                ? LetterRequest::with('letterType')->where('resident_id', $resident->id)->latest()->take(3)->get()
                : collect(),
            'complaints' => Complaint::where('user_id', $user->id)->latest()->take(3)->get(),
            'leaders' => app(\App\Services\GamificationService::class)->leaderboard(5),
        ]);
    }
}
