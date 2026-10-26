<?php

namespace App\Services;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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

    /**
     * Actualiza los datos personales (cuenta del productor) y los de la finca enviados.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Producer $producer, array $data): Producer
    {
        return DB::transaction(function () use ($producer, $data) {
            $userData = Arr::only($data, ['name', 'lastname', 'dni', 'phone', 'address', 'comunidad']);
            if ($userData !== [] && $producer->user) {
                $producer->user->update($userData);
            }

            $producerData = Arr::only($data, ['comunidad', 'farm_name', 'cows_count', 'daily_avg_liters', 'status']);
            if (array_key_exists('comunidad', $producerData)) {
                $producerData['zone'] = $producerData['comunidad'];
            }
            $producer->update($producerData);

            return $producer->fresh('user');
        });
    }

    /**
     * Elimina (borrado lógico) el productor y su cuenta solo si aún no tiene historial: las entregas,
     * pagos y sanciones de la web dejarían de encontrar a su productor. En ese caso se debe desactivar.
     */
    public function delete(Producer $producer): void
    {
        if ($producer->milkDeliveries()->exists() || $producer->payments()->exists() || $producer->sanctions()->exists()) {
            throw ValidationException::withMessages([
                'producer' => 'El productor tiene entregas, pagos o sanciones registradas. Desactívelo en lugar de eliminarlo.',
            ]);
        }

        DB::transaction(function () use ($producer) {
            $user = $producer->user;
            $producer->delete();
            $user?->tokens()->delete();
            $user?->delete();
        });
    }
}
