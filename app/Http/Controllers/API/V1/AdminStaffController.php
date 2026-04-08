<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCityAdminRequest;
use App\Http\Requests\Admin\UpdateCityAdminRequest;
use App\Models\Admin;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminStaffController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $admins = Admin::query()
            ->with('city')
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $current = $request->user('admin') ?? $request->user();

        return $this->success([
            'admins' => $admins->map(fn (Admin $admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'is_main' => $admin->is_main,
                'city_id' => $admin->city_id,
                'city_name' => $admin->city?->name,
                'can_edit' => ! $admin->is_main,
                'can_delete' => ! $admin->is_main && $current instanceof Admin && $admin->id !== $current->id,
            ])->values(),
        ], 'Staff list');
    }

    public function cityOptions(): JsonResponse
    {
        $cities = City::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name']);

        return $this->success($cities, 'Cities');
    }

    public function store(StoreCityAdminRequest $request): JsonResponse
    {
        $data = $request->validated();

        $admin = Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'city_id' => (int) $data['city_id'],
            'is_main' => false,
        ]);

        $admin->load('city');

        return $this->success([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'city_id' => $admin->city_id,
            'city_name' => $admin->city?->name,
        ], 'City administrator created', 201);
    }

    public function update(UpdateCityAdminRequest $request, Admin $admin): JsonResponse
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
        $admin->load('city');

        return $this->success([
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'city_id' => $admin->city_id,
            'city_name' => $admin->city?->name,
        ], 'Administrator updated');
    }

    public function destroy(Request $request, Admin $admin): JsonResponse
    {
        $current = $request->user('admin') ?? $request->user();

        abort_unless($current instanceof Admin && $current->isMainAdmin(), 403);
        abort_if($admin->is_main, 403);
        abort_if($admin->id === $current->id, 403);

        $admin->delete();

        return $this->success(null, 'Administrator removed');
    }
}
