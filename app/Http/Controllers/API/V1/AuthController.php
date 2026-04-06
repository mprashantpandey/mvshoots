<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\Concerns\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\API\V1\ProfileResource;
use App\Models\Owner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponse;

    public function syncUser(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'firebase_uid' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::updateOrCreate(
            ['phone' => $data['phone']],
            $data
        );

        return $this->success([
            'token' => $this->issueToken($user, 'user-app'),
            'user' => new ProfileResource($user),
        ], 'User profile synced');
    }

    public function syncPartner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email'],
            'firebase_uid' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $partner = Partner::updateOrCreate(
            ['phone' => $data['phone']],
            array_merge(['status' => 'active'], $data)
        );

        return $this->success([
            'token' => $this->issueToken($partner, 'partner-app'),
            'partner' => new ProfileResource($partner),
        ], 'Partner profile synced');
    }

    public function ownerLogin(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $owner = Owner::where('email', $data['email'])->first();

        if (! $owner || ! Hash::check($data['password'], $owner->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        $token = $owner->createToken('owner-app')->plainTextToken;

        return $this->success([
            'token' => $token,
            'owner' => new ProfileResource($owner),
        ], 'Owner logged in');
    }

    public function me(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User || $actor instanceof Partner || $actor instanceof Owner, 403, 'You are not authenticated.');

        return $this->success([
            'actor_type' => match (true) {
                $actor instanceof User => 'user',
                $actor instanceof Partner => 'partner',
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

        abort_unless($actor instanceof Owner, 403, 'Only owners can access this profile.');

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

        return $this->success(new ProfileResource($actor), 'Partner profile');
    }

    public function updateUserProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof User, 403, 'Only users can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
        ]);

        $actor->update($data);

        return $this->success(new ProfileResource($actor->fresh()), 'User profile updated');
    }

    public function updatePartnerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Partner, 403, 'Only partners can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
        ]);

        $actor->update($data);

        return $this->success(new ProfileResource($actor->fresh()), 'Partner profile updated');
    }

    public function updateOwnerProfile(Request $request): JsonResponse
    {
        $actor = $request->user('sanctum');

        abort_unless($actor instanceof Owner, 403, 'Only owners can update this profile.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
        ]);

        $actor->update($data);

        return $this->success(new ProfileResource($actor->fresh()), 'Owner profile updated');
    }

    private function issueToken(User|Partner $authenticatable, string $tokenName): string
    {
        $authenticatable->tokens()->delete();

        return $authenticatable->createToken($tokenName)->plainTextToken;
    }
}
