<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        $pages = Page::withCount('sections')->orderByRaw(
            "FIELD(slug, '".implode("','", array_keys(Page::PAGES))."')"
        )->get();

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(Page $page): View
    {
        $page->load('sections');

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $page->update($data);

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('status', "\"{$page->name}\" page updated successfully.");
    }
}
