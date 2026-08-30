<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        // The site-wide indexing toggle is a kill switch (see
        // layouts/public.blade.php) — no point listing anything here that
        // every page is now telling crawlers to skip.
        $indexingEnabled = setting('default_robots_index', true);

        if (! $indexingEnabled) {
            $pages = $areas = $posts = $categories = $tags = collect();
        } else {
            $pages = Page::where('is_active', true)->where('robots_index', true)->get();

            $areas = Area::active()->where('robots_index', true)->get(['slug', 'updated_at']);

            $posts = BlogPost::published()->where('robots_index', true)->get(['slug', 'updated_at']);

            $categories = BlogCategory::active()
                ->whereHas('posts', fn ($q) => $q->published())
                ->get(['slug', 'updated_at']);

            $tags = \App\Models\Tag::whereHas('posts', fn ($q) => $q->published())->get(['slug', 'updated_at']);
        }

        $xml = view('sitemap.index', compact('pages', 'areas', 'posts', 'categories', 'tags', 'indexingEnabled'))->render();

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /booking/invoice',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines), 200)->header('Content-Type', 'text/plain');
    }
}
