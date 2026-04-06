<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => Schema::hasTable('settings')
                ? Setting::value('app_name', 'VM Shoot')
                : 'VM Shoot',
            'auth' => [
                'admin' => $request->user('admin')
                    ? [
                        'name' => $request->user('admin')->name,
                        'email' => $request->user('admin')->email,
                    ]
                    : null,
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'errors' => fn () => $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->all()
                    : [],
            ],
        ];
    }
}
