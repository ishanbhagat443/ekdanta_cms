<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotFoundLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotFoundLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:seo.manage');
    }

    public function index(): View
    {
        return view('admin.not-found.index', [
            'logs' => NotFoundLog::query()
                ->where('resolved', false)
                ->orderByDesc('hits')
                ->paginate(20),
        ]);
    }

    public function dismiss(NotFoundLog $notFoundLog): RedirectResponse
    {
        $notFoundLog->update(['resolved' => true]);

        return back()->with('success', 'Marked as resolved.');
    }

    public function destroy(NotFoundLog $notFoundLog): RedirectResponse
    {
        $notFoundLog->delete();

        return back()->with('success', 'Removed from the list.');
    }
}
