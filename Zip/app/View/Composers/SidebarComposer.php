<?php

namespace App\View\Composers;

use App\Models\Disposition;
use App\Models\Mail;
use App\Models\NumberReservation;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarComposer
{
    private const CACHE_TTL_SECONDS = 60;

    public function compose(View $view): void
    {
        $user = auth()->user();

        if (!$user) {
            $view->with('sidebarCounts', [
                'pending_incoming'     => 0,
                'pending_dispositions' => 0,
                'pending_reservations' => 0,
            ]);

            return;
        }

        $sidebarCounts = Cache::remember(
            "sidebar_counts.user.{$user->id}",
            self::CACHE_TTL_SECONDS,
            fn () => [
                'pending_incoming' => Mail::incoming()->pending()->count(),
                'pending_dispositions' => Disposition::query()
                    ->where('to_user_id', $user->id)
                    ->where('status', 'pending')
                    ->count(),
                'pending_reservations' => $user->isAdmin()
                    ? NumberReservation::where('status', 'pending')->count()
                    : 0,
            ]
        );

        $view->with('sidebarCounts', $sidebarCounts);
    }
}
