<?php

namespace App\Services;

use App\Models\Producer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    /**
     * Crea un usuario del personal; si es productor, también su ficha de productor.
     *
     * @param  array<string, mixed>  $data  datos validados por StoreUserRequest
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $data['password'] = Hash::make($data['password']);
            $data['active'] ??= true;

            $user = User::create($data);

            if ($user->isProducer()) {
                Producer::create([
                    'user_id' => $user->id,
                    'code' => 'PROD-'.str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
                    'farm_name' => $data['name'],
                    'zone' => $data['comunidad'] ?? 'Huata',
                    'comunidad' => $data['comunidad'] ?? null,
                    'district' => 'Huata',
                    'province' => 'Huata',
                    'region' => 'Puno',
                    'status' => 'activo',
                    'registration_date' => now(),
                ]);
            }

            return $user;
        });
    }
}
