<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ReelRequest;
use App\Models\Category;
use App\Models\Reel;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReelController
{
    public function __construct(private readonly MediaUploadService $mediaUploadService)
    {
    }

    public function index(Request $request): Response
    {
        $reels = Reel::with('category')
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Reels/Index', [
            'reels' => $reels->through(fn (Reel $reel) => $this->transformReel($reel)),
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Reels/Form', [
            'reel' => null,
            'categories' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'submitUrl' => route('admin.reels.store'),
            'method' => 'post',
        ]);
    }

    public function store(ReelRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $thumbnail = $this->mediaUploadService->upload($request->file('thumbnail'), 'reels/thumbnails');
        $videoFile = $this->mediaUploadService->upload($request->file('video_file'), 'reels/videos');
        $data['thumbnail'] = $thumbnail ?? ($data['thumbnail'] ?? null);
        $data['video_url'] = $videoFile ?? ($data['video_url'] ?? null);
        unset($data['video_file']);

        Reel::create($data);

        return redirect()->route('admin.reels.index')->with('status', 'Reel created.');
    }

    public function show(Reel $reel): Response
    {
        $reel->load('category');

        return Inertia::render('Admin/Reels/Show', [
            'reel' => $this->transformReel($reel, true),
        ]);
    }

    public function edit(Reel $reel): Response
    {
        $reel->load('category');

        return Inertia::render('Admin/Reels/Form', [
            'reel' => $this->transformReel($reel, true),
            'categories' => Category::orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Category $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'submitUrl' => route('admin.reels.update', $reel),
            'method' => 'put',
        ]);
    }

    public function update(ReelRequest $request, Reel $reel): RedirectResponse
    {
        $data = $request->validated();
        $thumbnail = $this->mediaUploadService->upload($request->file('thumbnail'), 'reels/thumbnails');
        $videoFile = $this->mediaUploadService->upload($request->file('video_file'), 'reels/videos');
        $data['thumbnail'] = $thumbnail ?? ($data['thumbnail'] ?? $reel->thumbnail);
        $data['video_url'] = $videoFile ?? ($data['video_url'] ?? $reel->video_url);
        unset($data['video_file']);

        $reel->update($data);

        return redirect()->route('admin.reels.index')->with('status', 'Reel updated.');
    }

    public function destroy(Reel $reel): RedirectResponse
    {
        $reel->delete();

        return redirect()->route('admin.reels.index')->with('status', 'Reel deleted.');
    }

    private function transformReel(Reel $reel, bool $detailed = false): array
    {
        $thumbnailUrl = null;

        if ($reel->thumbnail) {
            $thumbnailUrl = str($reel->thumbnail)->startsWith('http')
                ? $reel->thumbnail
                : asset('storage/'.$reel->thumbnail);
        }

        $videoSource = null;

        if ($reel->video_url) {
            $videoSource = str($reel->video_url)->startsWith('http')
                ? $reel->video_url
                : asset('storage/'.$reel->video_url);
        }

        $payload = [
            'id' => $reel->id,
            'title' => $reel->title,
            'video_url' => $reel->video_url,
            'video_source' => $videoSource,
            'thumbnail' => $thumbnailUrl,
            'category_id' => $reel->category_id,
            'category_name' => $reel->category?->name,
            'status' => $reel->status,
            'created_at' => optional($reel->created_at)?->toDateTimeString(),
            'show_url' => route('admin.reels.show', $reel),
            'edit_url' => route('admin.reels.edit', $reel),
            'delete_url' => route('admin.reels.destroy', $reel),
        ];

        if ($detailed) {
            $payload['video_source'] = $videoSource;
        }

        return $payload;
    }
}
