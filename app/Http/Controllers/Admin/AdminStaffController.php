<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreCityAdminRequest;
use App\Http\Requests\Admin\UpdateCityAdminRequest;
use App\Models\Admin;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class AdminStaffController
{
    public function index(Request $request): Response
    {
        $admins = Admin::query()
            ->with('city')
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Staff/Index', [
            'admins' => $admins->map(fn (Admin $admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'is_main' => $admin->is_main,
                'city_id' => $admin->city_id,
                'city_name' => $admin->city?->name,
                'edit_url' => $admin->is_main ? null : route('admin.staff.edit', $admin),
                'delete_url' => $admin->is_main || $admin->id === Auth::guard('admin')->id()
                    ? null
                    : route('admin.staff.destroy', $admin),
            ])->values(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Staff/Form', [
            'adminUser' => null,
            'cities' => $this->cityOptions(),
            'submitUrl' => route('admin.staff.store'),
            'method' => 'post',
        ]);
    }

    public function store(StoreCityAdminRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'city_id' => (int) $data['city_id'],
            'is_main' => false,
        ]);

        return redirect()->route('admin.staff.index')->with('status', 'City administrator created. They can sign in at the same login page.');
    }

    public function edit(Admin $admin): Response
    {
        abort_if($admin->is_main, 404);

        return Inertia::render('Admin/Staff/Form', [
            'adminUser' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'city_id' => $admin->city_id,
            ],
            'cities' => $this->cityOptions(),
            'submitUrl' => route('admin.staff.update', $admin),
            'method' => 'put',
        ]);
    }

    public function update(UpdateCityAdminRequest $request, Admin $admin): RedirectResponse
    {
        abort_if($admin->is_main, 404);

        $data = $request->validated();
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'city_id' => (int) $data['city_id'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $admin->update($payload);

        return redirect()->route('admin.staff.index')->with('status', 'Administrator updated.');
    }

    public function destroy(Admin $admin): RedirectResponse
    {
        abort_if($admin->is_main, 403);
        abort_if($admin->id === Auth::guard('admin')->id(), 403);

        $admin->delete();

        return redirect()->route('admin.staff.index')->with('status', 'Administrator removed.');
    }

    /**
     * @return list<array{id: int, name: string}>
     */
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
