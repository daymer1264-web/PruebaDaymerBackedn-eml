<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     * Valida que el usuario autenticado tenga estado activo
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error' => 'Unauthenticated'
            ], 401);
        }

        $user = Auth::user();

        // Verificar si el usuario está activo
        if ($user->estado !== 'activo') {
            // Revocar el token actual del usuario inactivo
            try {
                $token = $request->user()->token();
                $token->revoke();
            } catch (\Exception $e) {
                // Si falla la revocación, continuar con el rechazo
                Log::error('Error revocando token: ' . $e->getMessage());
            }

            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta está inactiva. Contacta al administrador',
                'error' => 'User inactive'
            ], 403);
        }

        return $next($request);
    }
}