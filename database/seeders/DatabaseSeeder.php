<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Service;
use App\Models\Schedule;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================
        // Tenant
        // ==========================
        $tenant = Tenant::firstOrCreate(
            [
                'slug' => 'kingdom-barber-shop'
            ],
            [
                'name' => 'Kingdom Barber Shop',
                'phone' => '+595984863912',
                'whatsapp' => '+595984863912',
                'address' => 'Av. Japón, Encarnación 070121',
                'city' => 'Encarnación',
                'country' => 'Paraguay',
                'is_active' => true,
            ]
        );

        // ==========================
        // Usuario Administrador
        // ==========================
        $user = User::firstOrCreate(
            [
                'email' => 'kingdom@kingdombarbershop.com' // ← cambiá por el email real del dueño
            ],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Kingdom Barber Shop',
                'password' => bcrypt('123456'), // ← cambiar después del primer login
                'role' => 'owner',
                'is_active' => true,
            ]
        );

        // ==========================
        // Servicios
        // ==========================

        Service::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Corte de pelo'],
            [
                'description' => '',
                'duration_minutes' => 60,
                'price' => 50000,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Service::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Servicio completo'],
            [
                'description' => 'Corte, ceja y barba',
                'duration_minutes' => 60,
                'price' => 90000,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );

        Service::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Corte de niño'],
            [
                'description' => '',
                'duration_minutes' => 60,
                'price' => 50000,
                'is_active' => true,
                'sort_order' => 3,
            ]
        );

        Service::firstOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'Corte fade + freestyle'],
            [
                'description' => '',
                'duration_minutes' => 60,
                'price' => 60000,
                'is_active' => true,
                'sort_order' => 4,
            ]
        );

        // ==========================
        // Horarios
        // día: 0=domingo … 6=sábado
        // ==========================

        $horarios = [
            0 => [],                          // domingo: cerrado
            1 => [['14:00', '19:00']],        // lunes: solo tarde
            2 => [['10:00', '19:00']],        // martes
            3 => [['10:00', '19:00']],        // miércoles
            4 => [['10:00', '19:00']],        // jueves
            5 => [['10:00', '19:00']],        // viernes
            6 => [['10:00', '19:00']],        // sábado
        ];

        foreach ($horarios as $day => $rangos) {
            foreach ($rangos as [$apertura, $cierre]) {
                Schedule::firstOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'user_id' => $user->id,
                        'day_of_week' => $day,
                        'opens_at' => $apertura . ':00',
                    ],
                    [
                        'closes_at' => $cierre . ':00',
                        'is_active' => true,
                    ]
                );
            }
        }

        echo PHP_EOL;
        echo "======================================" . PHP_EOL;
        echo " Kingdom Barber Shop inicializado" . PHP_EOL;
        echo "======================================" . PHP_EOL;
        echo "Tenant ID: {$tenant->id}" . PHP_EOL;
        echo "User (barber) ID: {$user->id}" . PHP_EOL;
        echo "Servicios:" . PHP_EOL;
        foreach (Service::where('tenant_id', $tenant->id)->get(['id','name']) as $s) {
            echo "  - {$s->id}: {$s->name}" . PHP_EOL;
        }
        echo "======================================" . PHP_EOL;
    }
}
