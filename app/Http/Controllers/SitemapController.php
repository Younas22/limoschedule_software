<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function sitemap(): Response
    {
        $pages = Page::where('is_active', true)->get();

        $posts = BlogPost::published()->get(['slug', 'updated_at']);

        $categories = BlogCategory::active()
            ->whereHas('posts', fn ($q) => $q->published())
            ->get(['slug', 'updated_at']);

        $tags = \App\Models\Tag::whereHas('posts', fn ($q) => $q->published())->get(['slug', 'updated_at']);

        $xml = view('sitemap.index', compact('pages', 'posts', 'categories', 'tags'))->render();

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
