<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Support\Csv;
use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubscriberController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:enquiries.manage');
    }

    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();

        return view('admin.subscribers.index', [
            'subscribers' => Subscriber::query()
                ->when($search, fn ($q) => $q->where('email', 'like', "%{$search}%"))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function destroy(Subscriber $subscriber): RedirectResponse
    {
        $subscriber->delete();

        return back()->with('success', 'Subscriber removed.');
    }

    public function export(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            Csv::put($out, ['ID', 'Email', 'Status', 'IP', 'Subscribed']);

            Subscriber::query()->orderBy('id')->chunk(200, function ($chunk) use ($out): void {
                foreach ($chunk as $subscriber) {
                    Csv::put($out, [
                        $subscriber->id, $subscriber->email, $subscriber->status,
                        $subscriber->ip, $subscriber->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        }, 'newsletter-subscribers-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
