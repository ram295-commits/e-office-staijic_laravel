<?php

namespace App\Providers;

use App\Models\Disposition;
use App\Models\Mail;
use App\Models\NumberReservation;
use App\Observers\MailObserver;
use App\Policies\DispositionPolicy;
use App\Policies\MailPolicy;
use App\Policies\NumberReservationPolicy;
use App\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Disposition::class, DispositionPolicy::class);
        Gate::policy(Mail::class, MailPolicy::class);
        Gate::policy(NumberReservation::class, NumberReservationPolicy::class);

        Mail::observe(MailObserver::class);

        View::composer('layouts.app', SidebarComposer::class);

        if (str_starts_with(config('app.url'), 'https://')) {
            // Only force HTTPS if we are not running in a local host/development environment
            if (!app()->runningInConsole()) {
                $host = request()->getHost();
                $isLocal = in_array($host, ['localhost', '127.0.0.1'])
                    || preg_match('/^(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)/', $host)
                    || preg_match('/\.(local|test|dev)$/i', $host);

                if (!$isLocal) {
                    \Illuminate\Support\Facades\URL::forceScheme('https');
                }
            } else {
                \Illuminate\Support\Facades\URL::forceScheme('https');
            }
        }
    }
}
