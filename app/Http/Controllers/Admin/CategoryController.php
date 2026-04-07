<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\City;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController
{
    public function __construct(private readonly MediaUploadService $mediaUploadService)
    {
    }

    public function index(Request $request): Response
    {
        $categories = Category::query()
            ->with('cities')
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories->through(fn (Category $category) => $this->transformCategory($category)),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => null,
            'cities' => $this->cityOptions(),
            'submitUrl' => route('admin.categories.store'),
            'method' => 'post',
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'categories');
        $cityIds = $data['city_ids'] ?? [];
        unset($data['city_ids']);

        $category = Category::create($data);
        $category->cities()->sync($cityIds);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function show(Category $category): Response
    {
        $category->load('cities');

        return Inertia::render('Admin/Categories/Show', [
            'category' => $this->transformCategory($category),
        ]);
    }

    public function edit(Category $category): Response
    {
        $category->load('cities');

        return Inertia::render('Admin/Categories/Form', [
            'category' => $this->transformCategory($category),
            'cities' => $this->cityOptions(),
            'submitUrl' => route('admin.categories.update', $category),
            'method' => 'put',
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'categories') ?? $category->image;
        $cityIds = $data['city_ids'] ?? [];
        unset($data['city_ids']);

        $category->update($data);
        $category->cities()->sync($cityIds);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->cities()->detach();
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', 'Category deleted.');
    }

    private function transformCategory(Category $category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'description' => $category->description,
            'image' => $category->image ? asset('storage/'.$category->image) : null,
            'status' => $category->status,
            'city_ids' => $category->relationLoaded('cities') ? $category->cities->pluck('id')->all() : [],
            'cities' => $category->relationLoaded('cities') ? $category->cities->pluck('name')->values()->all() : [],
            'created_at' => optional($category->created_at)?->toDateTimeString(),
            'show_url' => route('admin.categories.show', $category),
            'edit_url' => route('admin.categories.edit', $category),
            'delete_url' => route('admin.categories.destroy', $category),
        ];
    }

    private function cityOptions(): array
    {
        return City::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (City $city) => [
                'id' => $city->id,
                'name' => $city->name,
            ])
            ->all();
    }
}
