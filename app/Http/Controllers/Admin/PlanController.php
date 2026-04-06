<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\PlanRequest;
use App\Models\Category;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlanController
{
    public function index(Request $request): Response
    {
        $plans = Plan::with('category')
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Plans/Index', [
            'plans' => $plans->through(fn (Plan $plan) => $this->transformPlan($plan)),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Plans/Form', [
            'plan' => null,
            'categories' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'submitUrl' => route('admin.plans.store'),
            'method' => 'post',
        ]);
    }

    public function store(PlanRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['inclusions'] = array_filter(array_map('trim', explode("\n", $data['inclusions'] ?? '')));

        Plan::create($data);

        return redirect()->route('admin.plans.index')->with('status', 'Plan created.');
    }

    public function show(Plan $plan): Response
    {
        $plan->load('category');

        return Inertia::render('Admin/Plans/Show', [
            'plan' => $this->transformPlan($plan, true),
        ]);
    }

    public function edit(Plan $plan): Response
    {
        $plan->load('category');

        return Inertia::render('Admin/Plans/Form', [
            'plan' => $this->transformPlan($plan, true),
            'categories' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'submitUrl' => route('admin.plans.update', $plan),
            'method' => 'put',
        ]);
    }

    public function update(PlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();

        $data['inclusions'] = array_filter(array_map('trim', explode("\n", $data['inclusions'] ?? '')));

        $plan->update($data);

        return redirect()->route('admin.plans.index')->with('status', 'Plan updated.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $plan->delete();

        return redirect()->route('admin.plans.index')->with('status', 'Plan deleted.');
    }

    private function transformPlan(Plan $plan, bool $detailed = false): array
    {
        $payload = [
            'id' => $plan->id,
            'category_id' => $plan->category_id,
            'category_name' => $plan->category?->name,
            'title' => $plan->title,
            'description' => $plan->description,
            'price' => (string) $plan->price,
            'duration' => $plan->duration,
            'status' => $plan->status,
            'created_at' => optional($plan->created_at)?->toDateTimeString(),
            'show_url' => route('admin.plans.show', $plan),
            'edit_url' => route('admin.plans.edit', $plan),
            'delete_url' => route('admin.plans.destroy', $plan),
        ];

        if ($detailed) {
            $payload['inclusions'] = $plan->inclusions ?? [];
        }

        return $payload;
    }
}
