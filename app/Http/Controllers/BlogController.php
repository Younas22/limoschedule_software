<?php

namespace App\Http\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $posts = BlogPost::with(['category', 'author', 'tags'])
            ->published()
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
            ))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'search' => $search,
            'featuredPosts' => $search ? collect() : BlogPost::published()->featured()->latest('published_at')->limit(3)->get(),
        ] + $this->sidebarData());
    }

    public function show(BlogPost $post): View|RedirectResponse
    {
        if (! $post->isPublished()) {
            abort(404);
        }

        DB::table('blog_posts')->where('id', $post->id)->increment('views_count');
        $post->views_count++;

        $post->load(['category', 'author', 'tags']);

        $related = BlogPost::published()
            ->where('blog_category_id', $post->blog_category_id)
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->limit(3)
            ->get();

        $previousPost = BlogPost::published()
            ->where('published_at', '<', $post->published_at)
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $nextPost = BlogPost::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at')
            ->orderBy('id')
            ->first();

        $jsonLd = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $post->title,
            'description' => $post->meta_description ?: $post->excerpt_or_summary,
            'image' => $post->featured_image_url,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $post->author?->name ?? setting('company_name', config('app.name'))],
            'publisher' => [
                '@type' => 'Organization',
                'name' => setting('company_name', config('app.name')),
                'logo' => ['@type' => 'ImageObject', 'url' => setting('logo_url')],
            ],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => url()->current()],
        ];

        return view('blog.show', [
            'post' => $post,
            'related' => $related,
            'previousPost' => $previousPost,
            'nextPost' => $nextPost,
            'jsonLd' => $jsonLd,
        ] + $this->sidebarData());
    }

    public function category(Request $request, BlogCategory $category): View
    {
        $search = $request->query('search');

        $posts = BlogPost::with(['category', 'author', 'tags'])
            ->published()
            ->where('blog_category_id', $category->id)
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
            ))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'search' => $search,
            'featuredPosts' => collect(),
            'activeCategory' => $category,
        ] + $this->sidebarData());
    }

    public function tag(Request $request, Tag $tag): View
    {
        $search = $request->query('search');

        $posts = BlogPost::with(['category', 'author', 'tags'])
            ->published()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', "%{$search}%")
                ->orWhere('excerpt', 'like', "%{$search}%")
            ))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'search' => $search,
            'featuredPosts' => collect(),
            'activeTag' => $tag,
        ] + $this->sidebarData());
    }

    /**
     * @return array<string, mixed>
     */
    private function sidebarData(): array
    {
        return [
            'categories' => BlogCategory::active()->withCount(['posts' => fn ($q) => $q->published()])->ordered()->get(),
            'popularPosts' => BlogPost::published()->popular()->limit(5)->get(),
            'tags' => Tag::withCount(['posts' => fn ($q) => $q->published()])->having('posts_count', '>', 0)->orderBy('name')->get(),
        ];
    }
}
