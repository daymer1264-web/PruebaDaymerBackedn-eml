<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *     title="API de Gestión de Usuarios",
 *     version="1.0.0",
 *     description="API para la gestión completa de usuarios del sistema"
 * )
 * 
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Servidor Local"
 * )
 * 
 * @OA\Tag(
 *     name="Usuarios",
 *     description="Operaciones relacionadas con usuarios"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 * )
 * 
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="Usuario",
 *     description="Modelo de usuario del sistema",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nombres", type="string", example="Juan Carlos"),
 *     @OA\Property(property="apellidos", type="string", example="Pérez García"),
 *     @OA\Property(property="nombre_completo", type="string", example="Juan Carlos Pérez García"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *     @OA\Property(property="telefono", type="string", example="+1234567890"),
 *     @OA\Property(property="estado", type="string", enum={"activo", "inactivo"}, example="activo"),
 *     @OA\Property(property="fecha_registro", type="string", format="date-time", example="2024-01-15 10:30:00"),
 *     @OA\Property(property="fecha_ultima_modificacion", type="string", format="date-time", example="2024-01-20 14:25:00")
 * )
 * 
 * @OA\Schema(
 *     schema="UserRequest",
 *     type="object",
 *     title="Solicitud de Usuario",
 *     required={"nombres", "apellidos", "email", "password"},
 *     @OA\Property(property="nombres", type="string", maxLength=255, example="Juan Carlos"),
 *     @OA\Property(property="apellidos", type="string", maxLength=255, example="Pérez García"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="juan.perez@example.com"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="password123"),
 *     @OA\Property(property="telefono", type="string", maxLength=20, example="+1234567890"),
 *     @OA\Property(property="estado", type="string", enum={"activo", "inactivo"}, example="activo")
 * )
 * 
 * @OA\Schema(
 *     schema="UserUpdateRequest",
 *     type="object",
 *     title="Solicitud de Actualización de Usuario",
 *     @OA\Property(property="nombres", type="string", maxLength=255, example="Juan Carlos"),
 *     @OA\Property(property="apellidos", type="string", maxLength=255, example="Pérez García"),
 *     @OA\Property(property="email", type="string", format="email", maxLength=255, example="juan.perez@example.com"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="nuevaPassword123"),
 *     @OA\Property(property="telefono", type="string", maxLength=20, example="+1234567890"),
 *     @OA\Property(property="estado", type="string", enum={"activo", "inactivo"}, example="activo")
 * )
 * 
 * @OA\Schema(
 *     schema="SuccessResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=true),
 *     @OA\Property(property="message", type="string", example="Operación exitosa"),
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="total", type="integer", example=10)
 * )
 * 
 * @OA\Schema(
 *     schema="ErrorResponse",
 *     type="object",
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="Error en la operación"),
 *     @OA\Property(property="error", type="string", example="Mensaje de error detallado")
 * )
 */

class UserController extends Controller
{
    /**
     * Listar todos los usuarios ordenados alfabéticamente
     * 
     * @OA\Get(
     *     path="/users",
     *     summary="Obtener lista de usuarios",
     *     description="Retorna una lista de todos los usuarios registrados en el sistema, ordenados alfabéticamente",
     *     tags={"Usuarios"},
     *     @OA\Parameter(
     *         name="estado",
     *         in="query",
     *         description="Filtrar usuarios por estado",
     *         required=false,
     *         @OA\Schema(type="string", enum={"activo", "inactivo"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios obtenida exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="data",
     *                         type="array",
     *                         @OA\Items(ref="#/components/schemas/User")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = User::query();

            // Filtrar por estado si se proporciona
            if ($request->has('estado')) {
                $query->where('estado', $request->estado);
            }

            // Ordenar alfabéticamente (A-Z)
            $users = $query->ordenAlfabetico()->get();

            // Formatear respuesta CON VERIFICACIÓN SEGURA
            $usersFormatted = $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado' => $user->estado,
                    'fecha_registro' => $user->fecha_registro ? $user->fecha_registro->format('Y-m-d H:i:s') : null,
                    'fecha_ultima_modificacion' => $user->fecha_ultima_modificacion ? $user->fecha_ultima_modificacion->format('Y-m-d H:i:s') : null,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Usuarios obtenidos exitosamente',
                'data' => $usersFormatted,
                'total' => $usersFormatted->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Crear un nuevo usuario
     * 
     * @OA\Post(
     *     path="/users",
     *     summary="Crear un nuevo usuario",
     *     description="Registra un nuevo usuario en el sistema",
     *     tags={"Usuarios"},
     *     security={},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del usuario a crear",
     *         @OA\JsonContent(ref="#/components/schemas/UserRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuario creado exitosamente",
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
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param StoreUserRequest $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            // Hashear la contraseña
            $data['password'] = Hash::make($data['password']);

            // Crear usuario
            $user = User::create($data);

            // RECARGAR el usuario para obtener las fechas correctamente desde la base de datos
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado' => $user->estado,
                    'fecha_registro' => $user->fecha_registro ? $user->fecha_registro->format('Y-m-d H:i:s') : null,
                    'fecha_ultima_modificacion' => $user->fecha_ultima_modificacion ? $user->fecha_ultima_modificacion->format('Y-m-d H:i:s') : null,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Mostrar un usuario específico
     * 
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Obtener un usuario específico",
     *     description="Retorna la información detallada de un usuario por su ID",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario obtenido exitosamente",
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
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

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
                    'fecha_registro' => $user->fecha_registro ? $user->fecha_registro->format('Y-m-d H:i:s') : null,
                    'fecha_ultima_modificacion' => $user->fecha_ultima_modificacion ? $user->fecha_ultima_modificacion->format('Y-m-d H:i:s') : null,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Actualizar un usuario existente
     * 
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Actualizar un usuario existente",
     *     description="Actualiza la información de un usuario específico",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario a actualizar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Datos del usuario a actualizar",
     *         @OA\JsonContent(ref="#/components/schemas/UserUpdateRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado exitosamente",
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
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param UpdateUserRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            
            $data = $request->validated();

            // Si se proporciona una nueva contraseña, hashearla
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Actualizar usuario (fecha_ultima_modificacion se actualiza automáticamente en el modelo)
            $user->update($data);

            // Recargar el modelo para obtener los datos actualizados
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado' => $user->estado,
                    'fecha_registro' => $user->fecha_registro ? $user->fecha_registro->format('Y-m-d H:i:s') : null,
                    'fecha_ultima_modificacion' => $user->fecha_ultima_modificacion ? $user->fecha_ultima_modificacion->format('Y-m-d H:i:s') : null,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Eliminar usuario (soft delete usando estado)
     * 
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Eliminar un usuario",
     *     description="Elimina un usuario cambiando su estado a 'inactivo' (soft delete)",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario a eliminar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado exitosamente",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="No se puede eliminar el propio usuario",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            $authenticatedUser = Auth::user();
            if ($authenticatedUser && $authenticatedUser->id === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puedes eliminar tu propio usuario'
                ], 400);
            }

            // Cambiar estado a inactivo en lugar de eliminar
            $user->update(['estado' => 'inactivo']);

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado exitosamente'
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Restaurar un usuario inactivo
     * 
     * @OA\Patch(
     *     path="/users/{id}/restore",
     *     summary="Restaurar un usuario inactivo",
     *     description="Restaura un usuario cambiando su estado de 'inactivo' a 'activo'",
     *     tags={"Usuarios"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario a restaurar",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario restaurado exitosamente",
     *         @OA\JsonContent(
     *             allOf={
     *                 @OA\Schema(ref="#/components/schemas/SuccessResponse"),
     *                 @OA\Schema(
     *                     type="object",
     *                     @OA\Property(
     *                         property="data",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="nombres", type="string"),
     *                         @OA\Property(property="apellidos", type="string"),
     *                         @OA\Property(property="nombre_completo", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="telefono", type="string"),
     *                         @OA\Property(property="estado", type="string")
     *                     )
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="El usuario ya está activo",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     *
     * @param int $id
     * @return JsonResponse
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            if ($user->estado === 'activo') {
                return response()->json([
                    'success' => false,
                    'message' => 'El usuario ya está activo'
                ], 400);
            }

            $user->update(['estado' => 'activo']);

            return response()->json([
                'success' => true,
                'message' => 'Usuario restaurado exitosamente',
                'data' => [
                    'id' => $user->id,
                    'nombres' => $user->nombres,
                    'apellidos' => $user->apellidos,
                    'nombre_completo' => $user->nombre_completo,
                    'email' => $user->email,
                    'telefono' => $user->telefono,
                    'estado' => $user->estado,
                ]
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar usuario',
                'error' => config('app.debug') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }
}