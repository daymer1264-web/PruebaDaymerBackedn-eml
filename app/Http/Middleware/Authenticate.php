<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        try {
            // Si no hay guards especificados, usar el guard por defecto
            $guards = empty($guards) ? ['api'] : $guards;

            // Verificar autenticación
            foreach ($guards as $guard) {
                if (auth()->guard($guard)->check()) {
                    // Usuario autenticado, continuar con la petición
                    auth()->shouldUse($guard);
                    return $next($request);
                }
            }

            // Si llegamos aquí, el usuario no está autenticado
            throw new AuthenticationException('Unauthenticated.', $guards);

        } catch (AuthenticationException $e) {
            return $this->unauthenticated($request, $e);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de autenticación',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated(Request $request, AuthenticationException $exception): Response
    {
        // Para peticiones API, retornar JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado. Por favor inicie sesión',
                'error' => 'Unauthenticated'
            ], 401);
        }

        // Para peticiones web, redirigir al login (opcional)
        return redirect()->guest(route('login'));
    }
}