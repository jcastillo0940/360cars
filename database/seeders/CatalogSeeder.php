<?php

namespace Database\Seeders;

use App\Models\LifestyleCategory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Toyota' => ['Corolla', 'RAV4', 'Hilux', 'Yaris', 'Agya', 'Fortuner', 'Land Cruiser Prado', 'Rush'],
            'Honda' => ['BR-V', 'Civic', 'City', 'CR-V', 'Fit', 'HR-V', 'Odyssey', 'Pilot'],
            'Hyundai' => ['Accent', 'Creta', 'Elantra', 'Grand i10', 'Palisade', 'Santa Fe', 'Staria', 'Tucson'],
            'Kia' => ['Carens', 'K3', 'Picanto', 'Rio', 'Seltos', 'Sorento', 'Sportage', 'Telluride'],
            'Nissan' => ['Frontier', 'Kicks', 'Pathfinder', 'Qashqai', 'Sentra', 'Versa', 'X-Trail', 'Xterra'],
            'Ford' => ['Bronco', 'Escape', 'Everest', 'Explorer', 'F-150', 'Maverick', 'Ranger', 'Territory'],
            'Chevrolet' => ['Captiva', 'Colorado', 'Groove', 'Onix', 'S10', 'Silverado', 'Tracker', 'Traverse'],
            'BMW' => ['118i', '320i', 'X1', 'X3', 'X5', 'X6'],
            'Mercedes-Benz' => ['A 200', 'C 200', 'CLA 200', 'GLA 200', 'GLC 300', 'GLE 450'],
            'Audi' => ['A3', 'A4', 'Q3', 'Q5', 'Q7', 'Q8'],
            'Volkswagen' => ['Amarok', 'Gol', 'Jetta', 'Nivus', 'Saveiro', 'Taos', 'T-Cross', 'Tiguan'],
            'Mazda' => ['BT-50', 'CX-3', 'CX-30', 'CX-5', 'CX-9', 'Mazda2', 'Mazda3'],
            'Mitsubishi' => ['ASX', 'Eclipse Cross', 'L200', 'Montero', 'Montero Sport', 'Outlander'],
            'Suzuki' => ['Celerio', 'Dzire', 'Ertiga', 'Grand Vitara', 'Jimny', 'S-Cross', 'Swift', 'Vitara'],
            'Isuzu' => ['D-Max', 'MU-X'],
            'Jeep' => ['Cherokee', 'Compass', 'Gladiator', 'Grand Cherokee', 'Renegade', 'Wrangler'],
            'Renault' => ['Duster', 'Kangoo', 'Koleos', 'Kwid', 'Logan', 'Oroch', 'Sandero', 'Stepway'],
            'Peugeot' => ['2008', '208', '3008', '5008', 'Landtrek', 'Partner'],
            'Fiat' => ['Argo', 'Cronos', 'Fiorino', 'Mobi', 'Pulse', 'Strada', 'Toro'],
            'Subaru' => ['Crosstrek', 'Forester', 'Impreza', 'Outback', 'XV'],
            'Lexus' => ['ES 250', 'GX 460', 'IS 300', 'NX 350', 'RX 350', 'UX 250h'],
            'Volvo' => ['S60', 'XC40', 'XC60', 'XC90'],
            'Land Rover' => ['Defender', 'Discovery', 'Discovery Sport', 'Range Rover Evoque', 'Range Rover Sport'],
            'Mini' => ['Clubman', 'Cooper', 'Countryman'],
            'Porsche' => ['Cayenne', 'Macan', 'Panamera'],
            'BYD' => ['Atto 3', 'Dolphin', 'Han', 'Seal', 'Song Pro', 'Yuan Plus'],
            'Changan' => ['Alsvin', 'CS15', 'CS35 Plus', 'CS55 Plus', 'Hunter', 'UNI-T'],
            'Chery' => ['Arrizo 5', 'Tiggo 2', 'Tiggo 4 Pro', 'Tiggo 7 Pro', 'Tiggo 8 Pro'],
            'Geely' => ['Azkarra', 'Coolray', 'Geometry C', 'GX3 Pro', 'Okavango', 'Starray'],
            'Great Wall' => ['Poer', 'Wingle 5'],
            'Haval' => ['H2', 'H6', 'Jolion'],
            'JAC' => ['JS2', 'JS4', 'JS8 Pro', 'S2', 'T6', 'T8'],
            'MG' => ['MG 3', 'MG 5', 'MG HS', 'MG RX5', 'MG ZS'],
            'DFSK' => ['Glory 330', 'Glory 500', 'Glory 560', 'Glory iX5'],
            'Dongfeng' => ['AX7', 'Rich 6', 'SX5'],
            'Foton' => ['Gratour', 'Tunland', 'View'],
            'Jetour' => ['Dashing', 'T2', 'X70', 'X90 Plus'],
            'BAIC' => ['BJ40', 'X25', 'X35', 'X55'],
        ];

        foreach ($catalog as $makeName => $models) {
            $make = VehicleMake::updateOrCreate(
                ['slug' => Str::slug($makeName)],
                ['name' => $makeName, 'is_active' => true],
            );

            foreach ($models as $modelName) {
                VehicleModel::updateOrCreate(
                    [
                        'vehicle_make_id' => $make->id,
                        'slug' => Str::slug($modelName),
                    ],
                    [
                        'name' => $modelName,
                        'is_active' => true,
                    ],
                );
            }
        }

        $categories = [
            ['name' => 'Autos para Uber', 'icon' => 'steering-wheel', 'description' => 'Modelos eficientes y comodos para trabajo diario.'],
            ['name' => 'Familiar', 'icon' => 'users', 'description' => 'Vehiculos con espacio, seguridad y comodidad.'],
            ['name' => 'Primer auto', 'icon' => 'sparkles', 'description' => 'Opciones faciles de manejar y mantener.'],
            ['name' => 'Aventurero', 'icon' => 'mountain', 'description' => 'SUV y pickups para caminos mixtos y escapadas.'],
        ];

        foreach ($categories as $category) {
            LifestyleCategory::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                $category,
            );
        }
    }
}
