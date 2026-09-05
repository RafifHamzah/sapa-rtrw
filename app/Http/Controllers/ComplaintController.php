<?php

namespace App\Http\Controllers;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Models\Complaint;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintController extends Controller
{
    /**
     * Daftar laporan milik warga (beserta status terkini).
     */
    public function index(Request $request): View
    {
        $complaints = Complaint::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return view('complaints.index', [
            'complaints' => $complaints,
            'categories' => ComplaintCategory::cases(),
        ]);
    }

    /**
     * Warga membuat laporan baru (judul, deskripsi, kategori, foto, lokasi).
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', Rule::enum(ComplaintCategory::class)],
            'location' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $resident = $request->user()->resident;

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('complaints', 'public')
            : null;

        Complaint::create([
            'rt_id' => $resident?->family?->rt_id ?? $request->user()->rt_id,
            'resident_id' => $resident?->id,
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'category' => $data['category'],
            'location' => $data['location'] ?? null,
            'photo_path' => $photoPath,
            'status' => ComplaintStatus::Open,
        ]);

        return back()
            ->with('status', 'Laporan berhasil dikirim. Pengurus akan menindaklanjuti.')
            ->with('celebrate', true);
    }

    /**
     * Detail laporan milik warga + timeline penanganan.
     */
    public function show(Request $request, Complaint $complaint): View
    {
        abort_unless($complaint->user_id === $request->user()->id, 403);

        $complaint->load(['updates.author', 'handler']);

        return view('complaints.show', ['complaint' => $complaint]);
    }
}
