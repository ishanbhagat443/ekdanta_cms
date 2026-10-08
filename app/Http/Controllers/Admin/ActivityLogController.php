<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:activity.view');
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'event' => ['nullable', 'in:'.implode(',', array_keys(ActivityLog::EVENTS))],
            'user_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return view('admin.activity.index', [
            'logs' => ActivityLog::query()
                ->filtered($filters)
                ->with('user:id,name')
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'filters' => $filters,
            'events' => ActivityLog::EVENTS,
            'users' => User::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }
}
