<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Subscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Footer newsletter form (enquiry stream #1 — kept separate from
     * contact enquiries per the requirement document).
     */
    public function store(Request $request): RedirectResponse
    {
        // Honeypot: real users never fill the hidden "website" field.
        if ($request->filled('website')) {
            return redirect()->back();
        }

        $data = $request->validate([
            'email' => ['required', 'email:rfc,strict', 'max:191'],
        ]);

        Subscriber::query()->firstOrCreate(
            ['email' => strtolower($data['email'])],
            ['ip' => $request->ip(), 'status' => 'active']
        );

        return redirect()->back()->with('newsletter_success', true);
    }
}
