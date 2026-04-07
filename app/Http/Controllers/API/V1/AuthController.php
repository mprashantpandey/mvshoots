<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ProfileResource;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\City;
use App\Models\DeviceToken;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function syncUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'firebase_uid' => ['nullable', 'string', 'max:255'],
        ]);

        $resolvedCity = $this->resolveCityPayload($data);

        $user = User::where('phone', $data['phone'])->first();

        if ($user) {
            $user->fill(array_filter([
                'firebase_uid' => $data['firebase_uid'] ?? null,
                'email' => $data['email'] ?? null,
                'city' => $resolvedCity['name'] ?? null,
                'city_id' => $resolvedCity['id'] ?? null,
            ], fn ($value) => filled($value)))->save();

            return $this->success([
                'token' => $this->issueToken($user->fresh(), 'user-app'),
                'user' => new ProfileResource($user->fresh()->load('managedCity')),
                'is_new' => false,
                'requires_registration' => false,
            ], 'User logged in');
        }

        if (blank($data['name'] ?? null) || blank($resolvedCity['name'] ?? ($data['city'] ?? null))) {
            return $this->success([
                'token' => null,
                'user' => null,
                'is_new' => true,
                'requires_registration' => true,
                'phone' => $data['phone'],
                'firebase_uid' => $data['firebase_uid'] ?? null,
            ], 'User registration required');
        }

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'city' => $resolvedCity['name'] ?? $data['city'],
            'city_id' => $resolvedCity['id'] ?? null,
            'firebase_uid' => $data['firebase_uid'] ?? null,
            'status' => 'active',
        ]);

        return $this->success([
            'token' => $this->issueToken($user, 'user-app'),
            'user' => new ProfileResource($user->load('managedCity')),
            'is_new' => true,
            'requires_registration' => false,
        ], 'User profile synced');
    }

    public function syncPartner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'firebase_uid' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $resolvedCity = $this->resolveCityPayload($data);

        $partner = Partner::where('phone', $data['phone'])->first();

        if ($partner) {
            $partner->fill(array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'city_id' => $resolvedCity['id'] ?? null,
                'firebase_uid' => $data['firebase_uid'] ?? null,
                'status' => $data['status'] ?? null,
            ], fn ($value) => filled($value)))->save();

            return $this->success([
                'token' => $this->issueToken($partner->fresh(), 'partner-app'),
                'partner' => new ProfileResource($this->hydratePartnerForProfile($partner->fresh())),
                'is_new' => false,
                'requires_registration' => false,
            ], 'Partner logged in');
        }

        if (blank($data['name'] ?? null)) {
            return $this->success([
                'token' => null,
                'partner' => null,
                'is_new' => true,
                'requires_registration' => true,
                'phone' => $data['phone'],
                'firebase_uid' => $data['firebase_uid'] ?? null,
            ], 'Partner registration required');
        }

        $partner = Partner::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'city_id' => $resolvedCity['id'] ?? null,
            'firebase_uid' => $data['firebase_uid'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return $this->success([
            'token' => $this->issueToken($partner, 'partner-app'),
            'partner' => new ProfileResource($this->hydratePartnerForProfile($partner->load(['managedCity', 'serviceCities', 'kyc']))),
            'is_new' => true,
            'requires_registration' => false,
        ], 'Partner profile synced');
    }

    public function ownerLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $owner = Owner::where('email', $data['email'])->first();

        if ($owner && Hash::check($data['password'], $owner->password)) {
            return $this->success([
                'token' => $owner->createToken('owner-app')->plainTextToken,
                'owner' => new ProfileResource($owner),
                'actor_type' => 'owner',
            ], 'Owner logged in');
        }

        $admin = Admin::where('email', $data['email'])->first();

        if ($admin && Hash::check($data['password'], $admin->password)) {
            return $this->success([
                'token' => $admin->createToken('admin-app')->plainTextToken,
                'owner' => new ProfileResource($admin),
                'actor_type' => 'admin',
            ], 'Admin logged in');
        }

        throw ValidationException::withMessages([
            'email' => ['Invalid credentials.'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User || $actor instanceof Partner || $actor instanceof Owner || $actor instanceof Admin, 403, 'You are not authenticated.');

        if ($actor instanceof User || $actor instanceof Partner) {
            $actor->loadMissing(['managedCity']);
        }

        if ($actor instanceof Partner) {
            $actor->loadMissing(['serviceCities', 'kyc']);
            $actor->loadCount('ratings');
            $actor->loadAvg('ratings', 'rating');
        }

        return $this->success([
            'actor_type' => match (true) {
                $actor instanceof User => 'user',
                $actor instanceof Partner => 'partner',
                $actor instanceof Admin => 'admin',
                default => 'owner',
            },
            'profile' => new ProfileResource($actor),
        ], 'Authenticated profile fetched');
    }

    public function ownerLogout(Request $request): JsonResponse
    {
        $request->user('sanctum')?->currentAccessToken()?->delete();

        return $this->success(null, 'Owner logged out');
    }

    public function ownerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can access this profile.');

        return $this->success(new ProfileResource($actor), 'Owner profile');
    }

    public function userProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can access this profile.');

        return $this->success(new ProfileResource($actor), 'User profile');
    }

    public function partnerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can access this profile.');

        $actor->load(['managedCity', 'serviceCities', 'kyc']);
        $actor->loadCount('ratings');
        $actor->loadAvg('ratings', 'rating');

        return $this->success(new ProfileResource($actor), 'Partner profile');
    }

    public function updateUserProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
        ]);

        $resolvedCity = $this->resolveCityPayload($data);

        $actor->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'city' => $resolvedCity['name'] ?? $data['city'] ?? null,
            'city_id' => $resolvedCity['id'] ?? null,
        ]);

        return $this->success(new ProfileResource($actor->fresh()->load('managedCity')), 'User profile updated');
    }

    public function deleteUserAccount(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can delete this account.');

        DeviceToken::query()
            ->where('user_type', 'user')
            ->where('user_id', $actor->id)
            ->delete();

        AppNotification::query()
            ->where('user_type', 'user')
            ->where('user_id', $actor->id)
            ->delete();

        $actor->tokens()->delete();
        $actor->delete();

        return $this->success(null, 'User account deleted');
    }

    public function updatePartnerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_id' => ['nullable', 'exists:cities,id'],
            'service_city_ids' => ['nullable', 'array'],
            'service_city_ids.*' => ['integer', 'exists:cities,id'],
        ]);

        $resolvedCity = $this->resolveCityPayload($data);

        $actor->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'city_id' => $resolvedCity['id'] ?? null,
        ]);

        if ($request->has('service_city_ids')) {
            $actor->serviceCities()->sync($data['service_city_ids'] ?? []);
        }

        $fresh = $actor->fresh()->load(['managedCity', 'serviceCities', 'kyc']);
        $fresh->loadCount('ratings');
        $fresh->loadAvg('ratings', 'rating');

        return $this->success(new ProfileResource($fresh), 'Partner profile updated');
    }

    public function deletePartnerAccount(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can delete this account.');

        DeviceToken::query()
            ->where('user_type', 'partner')
            ->where('user_id', $actor->id)
            ->delete();

        AppNotification::query()
            ->where('user_type', 'partner')
            ->where('user_id', $actor->id)
            ->delete();

        $actor->tokens()->delete();
        $actor->delete();

        return $this->success(null, 'Partner account deleted');
    }

    public function updateOwnerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        $actor->update($data);

        return $this->success(new ProfileResource($actor->fresh()), 'Owner profile updated');
    }

    public function updateOwnerPassword(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can update this password.');

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', Password::min(6), 'confirmed'],
        ]);

        if (! Hash::check($data['current_password'], $actor->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Your current password is incorrect.'],
            ]);
        }

        $actor->update([
            'password' => Hash::make($data['password']),
        ]);

        return $this->success(null, 'Password updated successfully');
    }

    public function deleteOwnerAccount(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner || $actor instanceof Admin, 403, 'Only admins can delete this account.');

        $userType = $actor instanceof Admin ? 'admin' : 'owner';

        DeviceToken::query()
            ->where('user_type', $userType)
            ->where('user_id', $actor->id)
            ->delete();

        AppNotification::query()
            ->where('user_type', $userType)
            ->where('user_id', $actor->id)
            ->delete();

        $actor->tokens()->delete();
        $actor->delete();

        return $this->success(null, 'Admin account deleted');
    }

    private function issueToken(User|Partner $authenticatable, string $tokenName): string
    {
        $authenticatable->tokens()->delete();

        return $authenticatable->createToken($tokenName)->plainTextToken;
    }

    private function hydratePartnerForProfile(Partner $partner): Partner
    {
        $partner->loadCount('ratings');
        $partner->loadAvg('ratings', 'rating');

        return $partner;
    }

    private function resolveCityPayload(array $data): array
    {
        if (! empty($data['city_id'])) {
            $city = City::find($data['city_id']);

            return [
                'id' => $city?->id,
                'name' => $city?->name,
            ];
        }

        $cityName = trim((string) ($data['city'] ?? ''));
        if ($cityName === '') {
            return ['id' => null, 'name' => null];
        }

        $city = City::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($cityName)])->first();

        return [
            'id' => $city?->id,
            'name' => $city?->name ?? $cityName,
        ];
    }
}
