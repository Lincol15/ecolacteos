<?php

namespace App\Services;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProducerService
{
    /**
     * Registrar un productor: crea su cuenta de usuario (rol productor) y su ficha.
     * Usado tanto por el panel web como por la API móvil.
     *
     * @param  array{name: string, lastname?: ?string, dni: string, email: string, phone: string, address?: ?string, comunidad: string, farm_name?: ?string, cows_count?: ?int, daily_avg_liters?: ?float, password: string}  $data
     */
    public function register(array $data): Producer
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'lastname' => $data['lastname'] ?? null,
                'dni' => $data['dni'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'comunidad' => $data['comunidad'],
                'password' => Hash::make($data['password']),
                'role' => 'productor',
                'active' => true,
            ]);

            return Producer::create([
                'user_id' => $user->id,
                'code' => 'PROD-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                'farm_name' => $data['farm_name'] ?? $data['name'],
                'zone' => $data['comunidad'],
                'comunidad' => $data['comunidad'],
                'district' => 'Huata',
                'province' => 'Huata',
                'region' => 'Puno',
                'cows_count' => $data['cows_count'] ?? 0,
                'daily_avg_liters' => $data['daily_avg_liters'] ?? 0,
                'status' => 'activo',
                'registration_date' => now(),
            ]);
        });
    }
}
