<?php

namespace App\Providers;

use App\Models\DataRequest;
use App\Models\UserRegistrationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Spatie\Activitylog\Models\Activity;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $approvalNotifications = [
                'data_requests' => 0,
                'user_registrations' => 0,
                'total' => 0,
            ];

            if (Auth::check() && Auth::user()->isSuperAdmin()) {
                $approvalNotifications['data_requests'] = DataRequest::whereIn('status', ['pending', 'bast_pending'])->count();
                $approvalNotifications['user_registrations'] = UserRegistrationRequest::where('status', 'pending')->count();
                $approvalNotifications['total'] = $approvalNotifications['data_requests'] + $approvalNotifications['user_registrations'];
            }

            $view->with('approvalNotifications', $approvalNotifications);
        });

        // Tambahkan IP dan User-Agent ke setiap activity log entry
        Activity::saving(function (Activity $activity) {
            $activity->properties = $activity->properties->merge([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        // Konfigurasi tambahan untuk keamanan
        if (app()->isProduction()) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
