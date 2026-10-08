<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\EnquiryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnquiryController extends Controller
{
    public function __construct(protected EnquiryService $service)
    {
        $this->middleware('can:enquiries.manage');
    }

    private function filters(Request $request): array
    {
        return $request->validate([
            'source' => ['nullable', 'in:'.implode(',', array_keys(Enquiry::SOURCES))],
            'status' => ['nullable', 'in:'.implode(',', Enquiry::STATUSES)],
            'q' => ['nullable', 'string', 'max:191'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);
    }

    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        return view('admin.enquiries.index', [
            'enquiries' => Enquiry::query()->filtered($filters)->latest('id')->paginate(20)->withQueryString(),
            'filters' => $filters,
            'sources' => Enquiry::SOURCES,
            'statuses' => Enquiry::STATUSES,
            'counts' => Enquiry::query()
                ->selectRaw("source, count(*) as total, sum(case when status = 'new' then 1 else 0 end) as new_count")
                ->groupBy('source')
                ->get()
                ->keyBy('source'),
        ]);
    }

    public function show(Enquiry $enquiry): View
    {
        if ($enquiry->status === 'new') {
            $enquiry->update(['status' => 'read']);
        }

        return view('admin.enquiries.show', [
            'enquiry' => $enquiry->load('submission'),
            'statuses' => Enquiry::STATUSES,
        ]);
    }

    public function update(Request $request, Enquiry $enquiry): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', Enquiry::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $enquiry->update($data);

        return back()->with('success', 'Enquiry updated.');
    }

    public function destroy(Enquiry $enquiry): RedirectResponse
    {
        $enquiry->delete();

        return redirect()->route('admin.enquiries.index')->with('success', 'Enquiry deleted.');
    }

    public function export(Request $request): StreamedResponse
    {
        return $this->service->exportCsv($this->filters($request));
    }
}
