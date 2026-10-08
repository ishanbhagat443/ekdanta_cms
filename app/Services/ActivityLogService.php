<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function log(string $event, ?int $userId, ?Request $request = null, array $extra = []): ActivityLog
    {
        $request ??= request();

        return ActivityLog::create([
            'user_id' => $userId,
            'event' => $event,
            'subject_type' => $extra['subject_type'] ?? null,
            'subject_id' => $extra['subject_id'] ?? null,
            'description' => $extra['description'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
        ]);
    }

    public function prune(int $days = 180): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($days))->delete();
    }
}
