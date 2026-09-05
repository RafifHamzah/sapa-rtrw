<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AnnouncementController extends Controller
{
    /**
     * Feed pengumuman warga: hanya yang sudah tayang, pinned dulu lalu terbaru.
     */
    public function index(): View
    {
        $announcements = Announcement::with('author')->published()->get();

        return view('announcements.index', ['announcements' => $announcements]);
    }

    /**
     * Detail satu pengumuman (hanya yang sudah tayang).
     */
    public function show(Announcement $announcement): View
    {
        if (! $announcement->isPublished()) {
            throw new NotFoundHttpException();
        }

        $announcement->load('author');

        return view('announcements.show', ['announcement' => $announcement]);
    }
}
