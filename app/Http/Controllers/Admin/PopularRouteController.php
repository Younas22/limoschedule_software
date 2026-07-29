<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PopularRoute;
use App\Models\RouteType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PopularRouteController extends Controller
{
    public function index(Request $request): View
    {
        $routes = PopularRoute::query()
            ->with('routeType')
            ->when($request->filled('route_type_id'), fn ($q) => $q->where('route_type_id', $request->query('route_type_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $routeTypes = RouteType::ordered()->get();

        return view('admin.popular-routes.index', compact('routes', 'routeTypes'));
    }

    public function create(): View
    {
        $routeTypes = RouteType::active()->ordered()->get();

        return view('admin.popular-routes.create', compact('routeTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRoute($request);

        PopularRoute::create($data + ['is_active' => true]);

        return redirect()
            ->route('admin.popular-routes.index')
            ->with('status', 'Route added successfully.');
    }

    public function edit(PopularRoute $popularRoute): View
    {
        $routeTypes = RouteType::active()->ordered()->get();

        return view('admin.popular-routes.edit', ['route' => $popularRoute, 'routeTypes' => $routeTypes]);
    }

    public function update(Request $request, PopularRoute $popularRoute): RedirectResponse
    {
        $popularRoute->update($this->validateRoute($request));

        return redirect()
            ->route('admin.popular-routes.index')
            ->with('status', 'Route updated successfully.');
    }

    public function destroy(PopularRoute $popularRoute): RedirectResponse
    {
        $popularRoute->delete();

        return back()->with('status', 'Route deleted successfully.');
    }

    public function toggleStatus(PopularRoute $popularRoute): RedirectResponse
    {
        $popularRoute->update(['is_active' => ! $popularRoute->is_active]);

        return back()->with('status', $popularRoute->is_active ? 'Route enabled.' : 'Route disabled.');
    }

    private function validateRoute(Request $request): array
    {
        return $request->validate([
            'route_type_id' => ['required', 'exists:route_types,id'],
            'pickup' => ['required', 'string', 'max:255'],
            'dropoff' => ['required', 'string', 'max:255'],
            'distance' => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'distance_unit' => ['required', 'in:km,mi'],
            'estimated_price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);
    }
}
