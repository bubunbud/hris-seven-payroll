<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;

class UserLoginActivityController extends Controller
{
    public function index(Request $request)
    {
        $activeWithinMinutes = max(1, min(120, (int) env('LOGIN_ACTIVE_WITHIN_MINUTES', 15)));
        $activeSince = now()->subMinutes($activeWithinMinutes);

        $activeSessions = UserSession::with('user')
            ->where('last_activity_at', '>=', $activeSince)
            ->orderByDesc('last_activity_at')
            ->get();

        $historyQuery = LoginHistory::with('user')->orderByDesc('created_at');

        if ($request->filled('user_id')) {
            $historyQuery->where('user_id', $request->user_id);
        }

        if ($request->filled('event')) {
            $historyQuery->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $historyQuery->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $historyQuery->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $history = $historyQuery->paginate(50)->withQueryString();

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('user-login-activity.index', compact(
            'activeSessions',
            'history',
            'users',
            'activeWithinMinutes'
        ));
    }
}
