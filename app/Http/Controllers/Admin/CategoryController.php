<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
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
            'submitUrl' => route('admin.categories.store'),
            'method' => 'post',
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'categories');

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category created.');
    }

    public function show(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Show', [
            'category' => $this->transformCategory($category),
        ]);
    }

    public function edit(Category $category): Response
    {
        return Inertia::render('Admin/Categories/Form', [
            'category' => $this->transformCategory($category),
            'submitUrl' => route('admin.categories.update', $category),
            'method' => 'put',
        ]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'categories') ?? $category->image;

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
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
            'created_at' => optional($category->created_at)?->toDateTimeString(),
            'show_url' => route('admin.categories.show', $category),
            'edit_url' => route('admin.categories.edit', $category),
            'delete_url' => route('admin.categories.destroy', $category),
        ];
    }
}
