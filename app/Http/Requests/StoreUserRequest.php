<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombres' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/' // Solo letras y espacios
            ],
            'apellidos' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/' // Solo letras y espacios
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                'unique:users,email',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/' // Formato email válido
            ],
            'telefono' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/' // Solo números y caracteres telefónicos
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/' // Al menos una mayúscula, minúscula y número
            ],
            'estado' => [
                'sometimes',
                'in:activo,inactivo'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'nombres.required' => 'Los nombres son obligatorios',
            'nombres.max' => 'Los nombres no deben exceder 100 caracteres',
            'nombres.regex' => 'Los nombres solo pueden contener letras y espacios',
            
            'apellidos.required' => 'Los apellidos son obligatorios',
            'apellidos.max' => 'Los apellidos no deben exceder 100 caracteres',
            'apellidos.regex' => 'Los apellidos solo pueden contener letras y espacios',
            
            'email.required' => 'El correo electrónico es obligatorio',
            'email.email' => 'El correo electrónico debe tener un formato válido',
            'email.max' => 'El correo electrónico no debe exceder 150 caracteres',
            'email.unique' => 'Este correo electrónico ya está registrado',
            'email.regex' => 'El formato del correo electrónico no es válido',
            
            'telefono.required' => 'El teléfono es obligatorio',
            'telefono.max' => 'El teléfono no debe exceder 20 caracteres',
            'telefono.regex' => 'El teléfono solo puede contener números y caracteres permitidos (+, -, espacios, paréntesis)',
            
            'password.required' => 'La contraseña es obligatoria',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
            
            'estado.in' => 'El estado debe ser activo o inactivo',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}