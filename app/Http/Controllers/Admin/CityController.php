<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CityController
{
    public function index(Request $request): Response
    {
        $cities = City::query()
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Cities/Index', [
            'cities' => $cities->through(fn (City $city) => $this->transformCity($city)),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Cities/Form', [
            'city' => null,
            'submitUrl' => route('admin.cities.store'),
            'method' => 'post',
        ]);
    }

    public function store(CityRequest $request): RedirectResponse
    {
        City::create([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.cities.index')->with('status', 'City created.');
    }

    public function show(City $city): Response
    {
        $city->loadCount(['users', 'bookings', 'categories', 'plans']);

        return Inertia::render('Admin/Cities/Show', [
            'city' => $this->transformCity($city, true),
        ]);
    }

    public function edit(City $city): Response
    {
        return Inertia::render('Admin/Cities/Form', [
            'city' => $this->transformCity($city),
            'submitUrl' => route('admin.cities.update', $city),
            'method' => 'put',
        ]);
    }

    public function update(CityRequest $request, City $city): RedirectResponse
    {
        $city->update([
            ...$request->validated(),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return redirect()->route('admin.cities.index')->with('status', 'City updated.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $city->delete();

        return redirect()->route('admin.cities.index')->with('status', 'City deleted.');
    }

    private function transformCity(City $city, bool $detailed = false): array
    {
        $payload = [
            'id' => $city->id,
            'name' => $city->name,
            'status' => $city->status,
            'sort_order' => (int) ($city->sort_order ?? 0),
            'created_at' => optional($city->created_at)?->toDateTimeString(),
            'show_url' => route('admin.cities.show', $city),
            'edit_url' => route('admin.cities.edit', $city),
            'delete_url' => route('admin.cities.destroy', $city),
        ];

        if ($detailed) {
            $payload['users_count'] = (int) ($city->users_count ?? 0);
            $payload['bookings_count'] = (int) ($city->bookings_count ?? 0);
            $payload['categories_count'] = (int) ($city->categories_count ?? 0);
            $payload['plans_count'] = (int) ($city->plans_count ?? 0);
        }

        return $payload;
    }
}
