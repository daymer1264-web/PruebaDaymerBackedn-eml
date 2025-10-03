<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuario administrador principal
        User::create([
            'nombres' => 'Admin',
            'apellidos' => 'EML',
            'email' => 'admin@eml.com',
            'telefono' => '+57 300 123 4567',
            'password' => Hash::make('Admin123'), // Cumple con las validaciones
            'estado' => 'activo',
            'fecha_registro' => now(),
            'fecha_ultima_modificacion' => now(),
        ]);

        // Usuarios de prueba adicionales
        User::create([
            'nombres' => 'Juan Carlos',
            'apellidos' => 'Pérez García',
            'email' => 'juan.perez@example.com',
            'telefono' => '+57 310 234 5678',
            'password' => Hash::make('Test123'),
            'estado' => 'activo',
            'fecha_registro' => now(),
            'fecha_ultima_modificacion' => now(),
        ]);

        User::create([
            'nombres' => 'María',
            'apellidos' => 'González López',
            'email' => 'maria.gonzalez@example.com',
            'telefono' => '+57 320 345 6789',
            'password' => Hash::make('Test123'),
            'estado' => 'activo',
            'fecha_registro' => now(),
            'fecha_ultima_modificacion' => now(),
        ]);

        User::create([
            'nombres' => 'Pedro',
            'apellidos' => 'Martínez Rodríguez',
            'email' => 'pedro.martinez@example.com',
            'telefono' => '+57 311 456 7890',
            'password' => Hash::make('Test123'),
            'estado' => 'inactivo',
            'fecha_registro' => now()->subDays(10),
            'fecha_ultima_modificacion' => now()->subDays(2),
        ]);

        $this->command->info('✓ Usuarios creados exitosamente');
        $this->command->info('→ Email: admin@eml.com | Password: Admin123');
    }
}