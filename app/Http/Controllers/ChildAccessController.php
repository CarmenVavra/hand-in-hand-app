<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureChildAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cookie;

class ChildAccessController extends Controller
{
    /** Schaltet dieses Gerät für den Kinderbereich frei (Cookie für 5 Jahre). */
    public function __invoke(string $token): RedirectResponse
    {
        $expected = config('handinhand.child_token');

        abort_unless($expected && hash_equals((string) $expected, $token), 404);

        Cookie::queue(Cookie::make(
            EnsureChildAccess::COOKIE,
            EnsureChildAccess::cookieValue((string) $expected),
            60 * 24 * 365 * 5,
            secure: request()->isSecure(),
            httpOnly: true,
            sameSite: 'lax',
        ));

        return redirect()->route('child.dashboard');
    }
}
