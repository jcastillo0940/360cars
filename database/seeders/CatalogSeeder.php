<?php

namespace Database\Seeders;

use App\Models\LifestyleCategory;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = [
            'Toyota' => ['Corolla', '4Runner', 'Agya', 'Auris', 'Avanza', 'C-HR', 'Coaster', 'Corolla Cross', 'Fortuner', 'FJ Cruiser', 'Granvia', 'Hiace', 'Hilux', 'Land Cruiser', 'Land Cruiser Prado', 'Raize', 'RAV4', 'Rush', 'Sequoia', 'Starlet', 'Tacoma', 'Tundra', 'Veloz', 'Yaris', 'Yaris Cross'],
            'Honda' => ['Accord', 'BR-V', 'City', 'Civic', 'CR-V', 'Fit', 'HR-V', 'Odyssey', 'Passport', 'Pilot', 'Ridgeline', 'WR-V'],
            'Hyundai' => ['Accent', 'Creta', 'Elantra', 'Grand i10', 'H-1', 'Kona', 'Palisade', 'Santa Cruz', 'Santa Fe', 'Staria', 'Tucson', 'Venue', 'Verna'],
            'Kia' => ['Carens', 'Carnival', 'EV5', 'EV6', 'K3', 'K5', 'Mohave', 'Niro', 'Picanto', 'Rio', 'Seltos', 'Sorento', 'Soul', 'Sportage', 'Telluride'],
            'Nissan' => ['370Z', 'Frontier', 'Juke', 'Kicks', 'March', 'Murano', 'Navara', 'Pathfinder', 'Patrol', 'Qashqai', 'Rogue', 'Sentra', 'Tiida', 'Urvan', 'Versa', 'X-Trail', 'Xterra'],
            'Mazda' => ['BT-50', 'CX-3', 'CX-30', 'CX-5', 'CX-50', 'CX-60', 'CX-9', 'CX-90', 'Mazda2', 'Mazda3', 'Mazda6'],
            'Mitsubishi' => ['ASX', 'Eclipse Cross', 'L200', 'Mirage', 'Montero', 'Montero Sport', 'Outlander', 'Outlander Sport', 'Xpander'],
            'Suzuki' => ['Baleno', 'Celerio', 'Dzire', 'Ertiga', 'Fronx', 'Grand Vitara', 'Ignis', 'Jimny', 'S-Cross', 'Swift', 'Vitara'],
            'Subaru' => ['BRZ', 'Crosstrek', 'Forester', 'Impreza', 'Legacy', 'Outback', 'WRX', 'XV'],
            'Isuzu' => ['D-Max', 'MU-X'],
            'Ford' => ['Bronco', 'Bronco Sport', 'EcoSport', 'Escape', 'Everest', 'Explorer', 'F-150', 'F-250', 'Maverick', 'Mustang', 'Ranger', 'Territory'],
            'Chevrolet' => ['Blazer', 'Captiva', 'Colorado', 'Equinox', 'Groove', 'N400', 'Onix', 'S10', 'Silverado', 'Suburban', 'Tahoe', 'Tracker', 'Traverse'],
            'GMC' => ['Acadia', 'Canyon', 'Sierra', 'Terrain', 'Yukon'],
            'Cadillac' => ['Escalade', 'XT4', 'XT5', 'XT6'],
            'Jeep' => ['Cherokee', 'Compass', 'Gladiator', 'Grand Cherokee', 'Renegade', 'Wagoneer', 'Wrangler'],
            'Dodge' => ['Attitude', 'Challenger', 'Charger', 'Durango', 'Journey', 'RAM 700', 'Vision'],
            'RAM' => ['700', '1000', '1500', '2500', 'ProMaster'],
            'Chrysler' => ['Pacifica', 'Town & Country'],
            'Volkswagen' => ['Amarok', 'CrossFox', 'Gol', 'Jetta', 'Nivus', 'Polo', 'Saveiro', 'Taos', 'T-Cross', 'Tiguan', 'Touareg', 'Virtus'],
            'Seat' => ['Arona', 'Ateca', 'Ibiza', 'Leon', 'Toledo'],
            'Skoda' => ['Fabia', 'Kamiq', 'Karoq', 'Kodiaq', 'Octavia', 'Superb'],
            'Audi' => ['A1', 'A3', 'A4', 'A5', 'A6', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8'],
            'BMW' => ['116i', '118i', '120i', '218i', '320i', '330e', '520i', 'X1', 'X2', 'X3', 'X4', 'X5', 'X6', 'X7'],
            'Mercedes-Benz' => ['A 200', 'A 250', 'B 200', 'C 200', 'C 300', 'CLA 200', 'CLA 250', 'E 200', 'E 300', 'GLA 200', 'GLB 200', 'GLC 300', 'GLE 450', 'Sprinter', 'Vito'],
            'Mini' => ['Clubman', 'Cooper', 'Cooper S', 'Countryman'],
            'Porsche' => ['718 Boxster', '718 Cayman', 'Cayenne', 'Macan', 'Panamera'],
            'Land Rover' => ['Defender', 'Discovery', 'Discovery Sport', 'Range Rover', 'Range Rover Evoque', 'Range Rover Sport', 'Range Rover Velar'],
            'Volvo' => ['C40', 'S60', 'S90', 'XC40', 'XC60', 'XC90'],
            'Lexus' => ['ES 250', 'GX 460', 'IS 300', 'LX 600', 'NX 350', 'RX 350', 'UX 250h'],
            'Acura' => ['ILX', 'MDX', 'RDX', 'TLX'],
            'Infiniti' => ['Q50', 'QX50', 'QX55', 'QX60', 'QX80'],
            'Jaguar' => ['E-Pace', 'F-Pace', 'XE', 'XF'],
            'Peugeot' => ['2008', '208', '3008', '301', '5008', 'Landtrek', 'Partner', 'Rifter'],
            'Renault' => ['Alaskan', 'Duster', 'Kangoo', 'Koleos', 'Kwid', 'Logan', 'Oroch', 'Sandero', 'Stepway'],
            'Citroen' => ['Berlingo', 'C3', 'C3 Aircross', 'C4 Cactus', 'C5 Aircross', 'Jumpy'],
            'Fiat' => ['500', 'Argo', 'Cronos', 'Doblo', 'Fiorino', 'Mobi', 'Pulse', 'Strada', 'Toro'],
            'Opel' => ['Crossland', 'Grandland', 'Mokka'],
            'Alfa Romeo' => ['Giulia', 'Stelvio'],
            'BYD' => ['Atto 3', 'Dolphin', 'Dolphin Mini', 'Han', 'King', 'Qin Plus', 'Seal', 'Seagull', 'Song Plus', 'Song Pro', 'Tang', 'Yuan Plus'],
            'Changan' => ['Alsvin', 'CS15', 'CS35', 'CS35 Plus', 'CS55 Plus', 'CS75 Plus', 'Eado Plus', 'Hunter', 'UNI-K', 'UNI-T', 'UNI-V'],
            'Chery' => ['Arrizo 5', 'Arrizo 8', 'Tiggo 2', 'Tiggo 3X', 'Tiggo 4 Pro', 'Tiggo 7 Pro', 'Tiggo 8 Pro'],
            'Omoda' => ['C5', 'E5'],
            'Jaecoo' => ['J7', 'J8'],
            'Geely' => ['Azkarra', 'Coolray', 'Emgrand', 'Geometry C', 'GX3 Pro', 'Okavango', 'Starray', 'Starray EM-i'],
            'Great Wall' => ['Poer', 'Tank 300', 'Wingle 5'],
            'GWM' => ['Ora 03', 'Poer', 'Tank 300', 'Wingle 7'],
            'Haval' => ['H2', 'H6', 'H6 HEV', 'Jolion', 'M6'],
            'JAC' => ['E10X', 'JS2', 'JS3', 'JS4', 'JS6', 'JS8 Pro', 'T6', 'T8'],
            'MG' => ['MG 3', 'MG 4', 'MG 5', 'MG GT', 'MG HS', 'MG One', 'MG RX5', 'MG ZS', 'Marvel R'],
            'DFSK' => ['C31', 'C32', 'Glory 330', 'Glory 500', 'Glory 560', 'Glory 580', 'Glory iX5'],
            'Dongfeng' => ['AX7', 'Rich 6', 'SX5', 'T5 Evo'],
            'Foton' => ['Gratour', 'Toano', 'Tunland', 'View'],
            'Jetour' => ['Dashing', 'T2', 'X70', 'X70 Plus', 'X90 Plus'],
            'BAIC' => ['BJ40', 'U5 Plus', 'X25', 'X35', 'X55', 'X7'],
            'FAW' => ['Bestune T33', 'Bestune T77', 'V80'],
            'SsangYong' => ['Korando', 'Musso', 'Rexton', 'Tivoli'],
            'Mahindra' => ['KUV100', 'Pik Up', 'Scorpio', 'XUV300', 'XUV500', 'XUV700'],
            'Maxus' => ['D60', 'T60', 'V80', 'eDeliver 9'],
            'Tesla' => ['Model 3', 'Model S', 'Model X', 'Model Y'],
        ];

        foreach ($catalog as $makeName => $models) {
            $make = VehicleMake::updateOrCreate(
                ['slug' => Str::slug($makeName)],
                ['name' => $makeName, 'is_active' => true],
            );

            $normalizedModels = Collection::make($models)
                ->filter()
                ->map(fn (string $model) => trim($model))
                ->unique()
                ->values();

            foreach ($normalizedModels as $modelName) {
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
