<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TrackUserSession
{
    /**
     * Update heartbeat untuk sesi terautentikasi (agar daftar "sedang aktif" akurat).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!Auth::check()) {
            return $response;
        }

        $sessionId = $request->session()->getId();
        if ($sessionId === '') {
            return $response;
        }

        $user = Auth::user();
        $existing = UserSession::where('session_id', $sessionId)->first();
        $now = now();

        if ($existing) {
            if ($existing->last_activity_at === null || $existing->last_activity_at->lt($now->copy()->subMinute())) {
                $existing->update([
                    'last_activity_at' => $now,
                    'ip_address' => $request->ip(),
                    'user_agent' => $this->truncateAgent($request->userAgent()),
                ]);
            }
        } else {
            UserSession::create([
                'session_id' => $sessionId,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $this->truncateAgent($request->userAgent()),
                'last_activity_at' => $now,
            ]);
        }

        return $response;
    }

    private function truncateAgent(?string $agent): ?string
    {
        if ($agent === null || $agent === '') {
            return null;
        }

        return mb_substr($agent, 0, 512);
    }
}
