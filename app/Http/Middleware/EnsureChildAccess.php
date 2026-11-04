<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schützt den Kinderbereich: Ist HIH_CHILD_TOKEN gesetzt, muss das Gerät einmalig den
 * Freischalt-Link (/zugang/<token>) geöffnet haben. Eingeloggte Admins kommen immer durch.
 */
class EnsureChildAccess
{
    public const COOKIE = 'hih_child';

    public function handle(Request $request, Closure $next): Response
    {
        $token = config('handinhand.child_token');

        if (! $token || Auth::check() || self::hasValidCookie($request, (string) $token)) {
            return $next($request);
        }

        return response()->view('errors.child-locked', status: 403);
    }

    public static function cookieValue(string $token): string
    {
        return hash_hmac('sha256', 'handinhand-child', $token);
    }

    private static function hasValidCookie(Request $request, string $token): bool
    {
        $cookie = $request->cookie(self::COOKIE);

        return is_string($cookie) && hash_equals(self::cookieValue($token), $cookie);
    }
}
