<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtener el ID de forma más robusta
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'nombres' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'apellidos' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($userId),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
            ],
            'telefono' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                'regex:/^[0-9+\-\s()]+$/'
            ],
            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/'
            ],
            'estado' => [
                'sometimes',
                'in:activo,inactivo'
            ]
        ];
    }

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
            
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número',
            
            'estado.in' => 'El estado debe ser activo o inactivo',
        ];
    }

    protected function prepareForValidation()
    {
        if ($this->password === null || $this->password === '') {
            $this->request->remove('password');
        }
    }

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