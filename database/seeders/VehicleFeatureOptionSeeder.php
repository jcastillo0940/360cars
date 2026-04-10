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
            'Asientos ElÃ©ctricos',
            'Bluetooth',
            'Bolsa(s) de Aire',
            'Caja de Cambios Dual',
            'CÃ¡mara 360',
            'CÃ¡mara de Retroceso',
            'Cierre Central',
            'Computadora de Viaje',
            'Control de Descenso',
            'Control de Radio en el Volante',
            'Control ElectrÃ³nico de Estabilidad',
            'Cruise Control',
            'DesempaÃ±ador Trasero',
            'DirecciÃ³n HidrÃ¡ulica/Electroasistida',
            'Disco Compacto (DVD)',
            'Espejos ElÃ©ctricos',
            'Frenos ABS',
            'HalÃ³genos',
            'Llave Inteligente/BotÃ³n de Arranque',
            'Luces de XenÃ³n/BixenÃ³n',
            'Monitor de PresiÃ³n de Llantas',
            'Radio con USB/AUX',
            'Retrovisores Auto-Retractibles',
            'RevisiÃ³n TÃ©cnica al dÃ­a',
            'Sensor de Lluvia',
            'Sensores de Retroceso',
            'Sensores Frontales',
            'Sunroof/techo panorÃ¡mico',
            'TapicerÃ­a de Cuero',
            'Turbo',
            'Vidrios ElÃ©ctricos',
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
