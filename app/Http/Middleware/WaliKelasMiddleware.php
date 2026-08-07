<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class WaliKelasMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            abort(403);
        }
        $teacher = $user->teacher;
        if (!$teacher || !$teacher->classroom_id) {
            abort(403, 'Anda bukan wali kelas.');
        }
        return $next($request);
    }
}
