<?php

namespace Database\Seeders;

use App\Models\VehicleFeatureOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VehicleFeatureOptionSeeder extends Seeder
{
    public function run(): void
    {
        $featureCatalog = [
            'Aire Acondicionado',
            'Aire Acondicionado Climatizado',
            'Alarma',
            'Aros de Lujo',
            'Asiento(s) con Memoria',
            'Asientos Eléctricos',
            'Bluetooth',
            'Bolsa(s) de Aire',
            'Caja de Cambios Dual',
            'Cámara 360',
            'Cámara de Retroceso',
            'Cierre Central',
            'Computadora de Viaje',
            'Control de Descenso',
            'Control de Radio en el Volante',
            'Control Electrónico de Estabilidad',
            'Cruise Control',
            'Desempañador Trasero',
            'Dirección Hidráulica/Electroasistida',
            'Disco Compacto (DVD)',
            'Espejos Eléctricos',
            'Frenos ABS',
            'Halógenos',
            'Llave Inteligente/Botón de Arranque',
            'Luces de Xenón/Bixenón',
            'Monitor de Presión de Llantas',
            'Radio con USB/AUX',
            'Retrovisores Auto-Retractibles',
            'Revisión Técnica al día',
            'Sensor de Lluvia',
            'Sensores de Retroceso',
            'Sensores Frontales',
            'Sunroof/techo panorámico',
            'Tapicería de Cuero',
            'Turbo',
            'Vidrios Eléctricos',
            'Vidrios Tintados',
            'Volante Ajustable',
            'Volante Multifuncional',
        ];

        foreach ($featureCatalog as $index => $name) {
            VehicleFeatureOption::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'category' => 'equipamiento',
                    'description' => null,
                    'sort_order' => ($index + 1) * 10,
                    'is_active' => true,
                ],
            );
        }

        VehicleFeatureOption::query()
            ->whereNotIn('slug', collect($featureCatalog)->map(fn (string $name) => Str::slug($name))->all())
            ->update(['is_active' => false]);
    }
}
