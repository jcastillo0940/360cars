<?php

namespace Database\Seeders;

use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsPostSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('account_type', 'admin')->first();

        if (!$admin) {
            return;
        }

        NewsPost::updateOrCreate(
            ['slug' => 'restriccion-vehicular-san-jose-costa-rica-2026'],
            [
                'user_id' => $admin->id,
                'title' => 'Guía Completa: Restricción Vehicular en San José 2026',
                'excerpt' => 'Todo lo que necesitas saber sobre los horarios, multas y excepciones de la restricción vehicular en el casco central de San José para este año.',
                'content' => "La restricción vehicular en Costa Rica es una medida clave para reducir el congestionamiento en la capital. \n\n### Horarios y Zonas\nLa restricción aplica de 6:00 AM a 7:00 PM de lunes a viernes, según el último número de la placa:\n- Lunes: 1 y 2\n- Martes: 3 y 4\n- Miércoles: 5 y 6\n- Jueves: 7 y 8\n- Viernes: 9 y 0\n\n### Multas Actualizadas\nEn 2026, la multa por irrespetar la restricción es de aproximadamente ₡26,000 colones más el costo del acarreo si aplica.\n\n### Excepciones\nRecuerda que los vehículos eléctricos y los que transportan personas con discapacidad están exentos, siempre que cuenten con el permiso correspondiente.",
                'cover_image_url' => '/news_restriccion_vehicular_2026.png',
                'status' => 'published',
                'is_featured' => true,
                'published_at' => now(),
                'meta_title' => 'Restricción Vehicular San José 2026 | Movikaa',
                'meta_description' => 'Consulta la tabla de restricción vehicular para San José, Costa Rica. Horarios por placa, multas y excepciones actualizadas.',
            ]
        );

        NewsPost::updateOrCreate(
            ['slug' => 'consejos-compra-auto-usado-costa-rica'],
            [
                'user_id' => $admin->id,
                'title' => '10 Consejos para comprar un auto usado en Costa Rica',
                'excerpt' => 'Evita estafas y sorpresas mecánicas con esta guía definitiva para validar vehículos usados en el mercado nacional.',
                'content' => "Comprar un auto usado requiere paciencia y ojo crítico. Aquí te dejamos 10 puntos vitales: \n\n1. Revisa el historial de RTVE (Dekra).\n2. Valida multas en el COSEVI.\n3. Verifica el historial de colisiones en el INS.\n4. Lleva un mecánico de confianza.\n5. Prueba el auto en frío.\n6. Valida que el vendedor sea el dueño registral.\n7. Revisa fugas de aceite.\n8. Verifica el estado de las llantas.\n9. Desconfía de precios demasiado bajos.\n10. Realiza el traspaso formal ante notario público.",
                'cover_image_url' => '/news_consejos_autos_usados.png',
                'status' => 'published',
                'is_featured' => false,
                'published_at' => now()->subDays(2),
                'meta_title' => 'Consejos Compra Auto Usado CR | Movikaa',
                'meta_description' => 'Guía práctica para comprar vehículos de segunda mano en Costa Rica sin riesgos.',
            ]
        );
    }
}
