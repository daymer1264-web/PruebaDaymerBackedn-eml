<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

/**
 * @OA\Tag(
 *     name="Autenticación",
 *     description="Endpoints para autenticación de usuarios"
 * )
 * 
 * @OA\Schema(
 *     schema="LoginRequest",
 *     type="object",
 *     required={"email", "password"},
 *     @OA\Property(property="email", type="string", format="email", example="usuario@example.com"),
 *     @OA\Property(property="password", type="string", format="password", example="password123")
 * )
 * 
 * @OA\Schema(
 *     schema="LoginResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Login exitoso"),
 *     @OA\Property(property="data", type="object",
 *         @OA\Property(property="user", ref="#/components/schemas/User"),
 *         @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."),
 *         @OA\Property(property="token_type", type="string", example="Bearer")
 *     )
 * )
 */
class AuthController extends Controller
{
    /**
     * Login de usuario
     * 
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario y retorna un token de acceso",
     *     tags={"Autenticación"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Credenciales de acceso",
     *         @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(ref="#/components/schemas/LoginResponse")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Usuario inactivo",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            // Verificar si el usuario existe y está activo
            $user = User::where('email', $credentials['email'])->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            if ($user->estado !== 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario inactivo. Contacte al administrador'
                ], 403);
            }

            // Intentar autenticar
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas'
                ], 401);
            }

            // Generar token de acceso con Passport
            $token = $user->createToken('auth_token')->accessToken;

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nombres' => $user->nombres,
                        'apellidos' => $user->apellidos,
                        'nombre_completo' => $user->nombre_completo,
                        'email' => $user->email,
                        'telefono' => $user->telefono,
                        'estado' => $user->estado,
                        'fecha_registro' => $user->fecha_registro->format('Y-m-d H:i:s'),
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer'
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Logout de usuario
     * 
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Cerrar sesión",
     *     description="Revoca el token de acceso del usuario actual",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout exitoso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        try {
            // Obtener el token actual y revocarlo
            $token = request()->user()->token();
            $token->revoke();

            return response()->json([
                'success' => true,
                'message' => 'Logout exitoso'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Obtener información del usuario autenticado
     * 
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Obtener usuario actual",
     *     description="Retorna la información del usuario autenticado",
     *     tags={"Autenticación"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Información del usuario obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(property="data", ref="#/components/schemas/User")
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @return JsonResponse
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado' => $user->estado,
                    'fecha_registro' => $user->fecha_registro->format('Y-m-d H:i:s'),
                    'fecha_ultima_modificacion' => $user->fecha_ultima_modificacion->format('Y-m-d H:i:s'),
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error en el servidor',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }
}