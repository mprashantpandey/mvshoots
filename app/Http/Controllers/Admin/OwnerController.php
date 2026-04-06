<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\OwnerRequest;
use App\Models\Owner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class OwnerController
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'status']);

        $owners = Owner::query()
            ->when($request->string('search')->value(), function ($query, $search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->string('status')->value(), function ($query, $status): void {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Owners/Index', [
            'owners' => $owners->through(fn (Owner $owner) => $this->transformOwner($owner)),
            'filters' => $filters,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Owners/Form', [
            'owner' => null,
            'submitUrl' => route('admin.owners.store'),
            'method' => 'post',
        ]);
    }

    public function store(OwnerRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        Owner::create($data);

        return redirect()->route('admin.owners.index')->with('status', 'Owner created.');
    }

    public function show(Owner $owner): Response
    {
        return Inertia::render('Admin/Owners/Show', [
            'owner' => $this->transformOwner($owner),
        ]);
    }

    public function edit(Owner $owner): Response
    {
        return Inertia::render('Admin/Owners/Form', [
            'owner' => $this->transformOwner($owner),
            'submitUrl' => route('admin.owners.update', $owner),
            'method' => 'put',
        ]);
    }

    public function update(OwnerRequest $request, Owner $owner): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $owner->update($data);

        return redirect()->route('admin.owners.index')->with('status', 'Owner updated.');
    }

    public function updateStatus(Request $request, Owner $owner): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:active,inactive'],
        ]);

        $owner->update([
            'status' => $data['status'],
        ]);

        return redirect()->back()->with('status', 'Owner status updated.');
    }

    public function destroy(Owner $owner): RedirectResponse
    {
        $owner->delete();

        return redirect()->route('admin.owners.index')->with('status', 'Owner deleted.');
    }

    private function transformOwner(Owner $owner): array
    {
        return [
            'id' => $owner->id,
            'name' => $owner->name,
            'email' => $owner->email,
            'status' => $owner->status,
            'show_url' => route('admin.owners.show', $owner),
            'edit_url' => route('admin.owners.edit', $owner),
            'delete_url' => route('admin.owners.destroy', $owner),
            'status_url' => route('admin.owners.update-status', $owner),
        ];
    }
}
