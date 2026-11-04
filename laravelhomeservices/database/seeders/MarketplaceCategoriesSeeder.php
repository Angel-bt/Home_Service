<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Oficios reales del valle para producción. Se puede correr más de una vez: no duplica ni pisa
 * lo que la administración ya editó. La imagen solo se ve en el panel de administración.
 */
class MarketplaceCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'gasfiteria' => ['Gasfitería', '1521969409.png'],
            'electricidad' => ['Electricidad', '1521969419.png'],
            'jardineria' => ['Jardinería', '1651835715.png'],
            'albanileria' => ['Albañilería', 'gypsum.jpg'],
            'limpieza' => ['Limpieza', '1521969446.png'],
            'pintura' => ['Pintura', '1521972643.png'],
            'fumigacion' => ['Fumigación', '1521969464.png'],
            'piscinas' => ['Piscinas', '1521969446.png'],
            'carpinteria' => ['Carpintería', 'gypsum.jpg'],
            'cerrajeria' => ['Cerrajería', '1521969409.png'],
            'aire-acondicionado' => ['Aire acondicionado', '1521969419.png'],
            'electrodomesticos' => ['Electrodomésticos', '1521969419.png'],
            'mudanzas' => ['Mudanzas y fletes', 'gypsum.jpg'],
            'otros-oficios' => ['Otros oficios', 'gypsum.jpg'],
        ] as $slug => [$name, $image]) {
            ServiceCategory::firstOrCreate(['slug' => $slug], ['name' => $name, 'image' => $image, 'featured' => true]);
        }
    }
}
