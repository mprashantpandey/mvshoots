<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SliderRequest;
use App\Models\Slider;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SliderController
{
    public function __construct(private readonly MediaUploadService $mediaUploadService)
    {
    }

    public function index(Request $request): Response
    {
        $sliders = Slider::query()
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('subtitle', 'like', "%{$search}%");
                });
            })
            ->when($request->string('app_target')->value(), function ($query, $target): void {
                $query->where('app_target', $target);
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->orderBy('sort_order')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Sliders/Index', [
            'sliders' => $sliders->through(fn (Slider $slider) => $this->transformSlider($slider)),
            'filters' => $request->only(['search', 'app_target', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Sliders/Form', [
            'slider' => null,
            'submitUrl' => route('admin.sliders.store'),
            'method' => 'post',
        ]);
    }

    public function store(SliderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'sliders') ?? '';
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Slider::create($data);

        return redirect()->route('admin.sliders.index')->with('status', 'Slider created.');
    }

    public function edit(Slider $slider): Response
    {
        return Inertia::render('Admin/Sliders/Form', [
            'slider' => $this->transformSlider($slider, true),
            'submitUrl' => route('admin.sliders.update', $slider),
            'method' => 'put',
        ]);
    }

    public function update(SliderRequest $request, Slider $slider): RedirectResponse
    {
        $data = $request->validated();
        $data['image'] = $this->mediaUploadService->upload($request->file('image'), 'sliders') ?? $slider->image;
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $slider->update($data);

        return redirect()->route('admin.sliders.index')->with('status', 'Slider updated.');
    }

    public function destroy(Slider $slider): RedirectResponse
    {
        $slider->delete();

        return redirect()->route('admin.sliders.index')->with('status', 'Slider deleted.');
    }

    private function transformSlider(Slider $slider, bool $detailed = false): array
    {
        $imageUrl = null;

        if ($slider->image) {
            $imageUrl = str($slider->image)->startsWith('http')
                ? $slider->image
                : asset('storage/'.$slider->image);
        }

        $payload = [
            'id' => $slider->id,
            'title' => $slider->title,
            'subtitle' => $slider->subtitle,
            'app_target' => $slider->app_target,
            'sort_order' => $slider->sort_order,
            'status' => $slider->status,
            'image' => $imageUrl,
            'edit_url' => route('admin.sliders.edit', $slider),
            'delete_url' => route('admin.sliders.destroy', $slider),
        ];

        if ($detailed) {
            $payload['created_at'] = optional($slider->created_at)?->toDateTimeString();
        }

        return $payload;
    }
}
