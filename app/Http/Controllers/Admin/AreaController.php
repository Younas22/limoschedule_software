<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        $areas = Area::ordered()->get();

        return view('admin.areas.index', compact('areas'));
    }

    public function create(): View
    {
        return view('admin.areas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateArea($request);

        $area = Area::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.areas.index')
            ->with('status', "Area \"{$area->name}\" added successfully.");
    }

    public function edit(Area $area): View
    {
        return view('admin.areas.edit', compact('area'));
    }

    public function update(Request $request, Area $area): RedirectResponse
    {
        $data = $this->validateArea($request, $area);
        $data['slug'] = $area->uniqueSlug($data['name']);

        $area->update($data);

        return redirect()
            ->route('admin.areas.index')
            ->with('status', "Area \"{$area->name}\" updated successfully.");
    }

    public function destroy(Area $area): RedirectResponse
    {
        $area->delete();

        return back()->with('status', 'Area deleted successfully.');
    }

    public function toggleStatus(Area $area): RedirectResponse
    {
        $area->update(['is_active' => ! $area->is_active]);

        return back()->with('status', $area->is_active ? "\"{$area->name}\" enabled." : "\"{$area->name}\" disabled.");
    }

    public function moveUp(Area $area): RedirectResponse
    {
        $previous = Area::where('sort_order', '<', $area->sort_order)->orderByDesc('sort_order')->first();

        if ($previous) {
            DB::transaction(function () use ($area, $previous) {
                [$area->sort_order, $previous->sort_order] = [$previous->sort_order, $area->sort_order];
                $area->save();
                $previous->save();
            });
        }

        return back();
    }

    public function moveDown(Area $area): RedirectResponse
    {
        $next = Area::where('sort_order', '>', $area->sort_order)->orderBy('sort_order')->first();

        if ($next) {
            DB::transaction(function () use ($area, $next) {
                [$area->sort_order, $next->sort_order] = [$next->sort_order, $area->sort_order];
                $area->save();
                $next->save();
            });
        }

        return back();
    }

    private function validateArea(Request $request, ?Area $area = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
