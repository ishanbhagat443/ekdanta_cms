<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function __construct(protected SeoService $seo)
    {
    }

    public function sitemap(): Response
    {
        return response($this->seo->sitemapXml(), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    public function robots(): Response
    {
        return response($this->seo->robotsTxt(), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
