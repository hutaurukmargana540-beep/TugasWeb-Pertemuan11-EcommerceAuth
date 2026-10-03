<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * REQ 6: middleware kustom. Dipakai di route sebagai  ->middleware('role:admin,editor')
 * (alias "role" didaftarkan di bootstrap/app.php).
 * Pengguna belum login sudah ditangani middleware "auth" yang dipasang lebih dulu.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless(
            $user && $user->hasRole(...$roles),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );

        return $next($request);
    }
}
