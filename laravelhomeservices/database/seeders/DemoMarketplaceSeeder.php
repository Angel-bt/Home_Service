<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Local demo data for trying the marketplace end to end. Never run in production.
 *
 *   php artisan db:seed --class=DemoMarketplaceSeeder
 *
 * Accounts (password "password"): admin@proyetech.test, proveedor@proyetech.test,
 * proveedora@proyetech.test, cliente@proyetech.test.
 */
class DemoMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DemoMarketplaceSeeder is for local development only.');
        }

        $categories = [];
        foreach ([
            'gasfiteria' => ['Gasfitería', '1521969409.png'],
            'electricidad' => ['Electricidad', '1521969419.png'],
            'jardineria' => ['Jardinería', '1651835715.png'],
            'albanileria' => ['Albañilería', 'gypsum.jpg'],
            'limpieza' => ['Limpieza', '1521969446.png'],
            'fumigacion' => ['Fumigación', '1521969464.png'],
            'pintura' => ['Pintura', '1521972643.png'],
        ] as $slug => [$name, $image]) {
            $category = ServiceCategory::firstOrNew(['slug' => $slug]);
            $category->name = $name;
            $category->image = $image;
            $category->featured = true;
            $category->save();
            $categories[$slug] = $category;
        }

        $admin = $this->user('admin@proyetech.test', 'Administración Malakplace', 'ADM', '0995528556');
        $pedro = $this->provider('proveedor@proyetech.test', 'Pedro Jaramillo', '0991112233', ['malacatos', 'vilcabamba', 'quinara'], 'Gasfitero y electricista con 10 años de experiencia en quintas del valle.');
        $rosa = $this->provider('proveedora@proyetech.test', 'Rosa Cueva', '0984445566', ['malacatos', 'yangana'], 'Jardinería, mantenimiento de piscinas y limpieza de quintas.');
        $cliente = $this->user('cliente@proyetech.test', 'Carlos Ordóñez', 'CST', '0987654321');

        $services = [
            [$pedro, 'gasfiteria', 'Destape y reparación de tuberías', 'Atención el mismo día en Malacatos', 15, '1651851600.jpg'],
            [$pedro, 'electricidad', 'Instalaciones eléctricas para quintas', 'Revisión, tableros y puntos de luz', 25, '1734576876.jpg'],
            [$rosa, 'jardineria', 'Mantenimiento de jardines', 'Poda, corte de césped y limpieza', 20, '1735790763.jpg'],
            [$rosa, 'limpieza', 'Limpieza profunda de quintas', 'Ideal antes y después de temporada', 35, 'service_10.jpg'],
            [$admin, 'fumigacion', 'Fumigación de viviendas', 'Control de plagas con productos certificados', 30, 'service_11.jpg'],
        ];

        foreach ($services as [$owner, $category, $name, $tagline, $price, $image]) {
            $service = Service::firstOrNew(['slug' => \Illuminate\Support\Str::slug($name)]);
            $service->forceFill([
                'user_id' => $owner->id,
                'name' => $name,
                'tagline' => $tagline,
                'service_category_id' => $categories[$category]->id,
                'price' => $price,
                'featured' => true,
                'description' => $tagline . '. Trabajo garantizado y precios justos para Malacatos y el valle.',
                'inclusion' => 'Mano de obra|Revisión inicial|Limpieza al terminar',
                'exclusion' => 'Materiales y repuestos|Transporte fuera de la zona',
                'image' => $image,
                'thumbnail' => $image,
            ])->save();
        }

        $destape = Service::where('slug', 'destape-y-reparacion-de-tuberias')->firstOrFail();
        $request = ServiceRequest::firstOrCreate(
            ['service_id' => $destape->id, 'customer_id' => $cliente->id],
            ['provider_id' => $pedro->id, 'name' => $cliente->name, 'phone' => $cliente->phone, 'zone' => 'malacatos', 'address' => 'Barrio Landangui, quinta El Arupo', 'message' => 'Se tapó el desagüe de la cocina.', 'status' => ServiceRequest::COMPLETED]
        );
        Review::firstOrCreate(
            ['service_request_id' => $request->id],
            ['service_id' => $destape->id, 'user_id' => $cliente->id, 'rating' => 5, 'comment' => 'Llegó a la hora acordada y dejó todo limpio. Muy recomendado.']
        );
    }

    private function user(string $email, string $name, string $type, string $phone): User
    {
        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'utype' => $type,
            'phone' => $phone,
            'password' => $user->exists ? $user->password : Hash::make('password'),
            'email_verified_at' => now(),
        ])->save();

        return $user;
    }

    private function provider(string $email, string $name, string $whatsapp, array $zones, string $about): User
    {
        $user = $this->user($email, $name, 'SVP', $whatsapp);
        $profile = ServiceProvider::firstOrCreate(['user_id' => $user->id]);
        $profile->whatsapp = $whatsapp;
        $profile->about = $about;
        $profile->city = 'Malacatos';
        $profile->save();
        $profile->syncZones($zones);

        return $user;
    }
}
