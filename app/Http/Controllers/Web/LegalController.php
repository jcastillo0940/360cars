<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Seo\SeoService;
use Illuminate\Contracts\View\View;

class LegalController extends Controller
{
    public function __construct(
        private readonly SeoService $seoService,
    ) {
    }

    public function terms(): View
    {
        $pageTitle = 'Terminos y condiciones';
        $pageDescription = 'Condiciones generales para el uso de Movikaa como plataforma de compra, venta y publicacion de vehiculos en Costa Rica.';
        $operatorName = $this->legalOperatorName();

        return $this->renderLegalPage('legal.terms', $pageTitle, $pageDescription, [
            [
                'heading' => 'Datos del operador',
                'body' => [
                    sprintf('La plataforma se identifica comercialmente como Movikaa y, salvo indicacion distinta en una publicacion o contrato especifico, es operada bajo la marca %s.', $operatorName),
                    sprintf('Para consultas legales o comerciales relacionadas con estos terminos puedes comunicarte a %s.', config('mail.from.address', 'soporte@movikaa.co')),
                ],
            ],
            [
                'heading' => '1. Alcance del servicio',
                'body' => [
                    'Movikaa es una plataforma digital que facilita la publicacion, promocion y consulta de anuncios de vehiculos nuevos y usados dentro de Costa Rica.',
                    'Movikaa no es parte directa de la compraventa entre usuarios, salvo cuando una oferta indique expresamente que el vehiculo es propiedad, consignacion o inventario administrado por la empresa o un aliado identificado dentro de la publicacion.',
                ],
            ],
            [
                'heading' => '2. Aceptacion',
                'body' => [
                    'Al acceder, navegar, publicar un anuncio, crear una cuenta o contactar a otro usuario por medio de Movikaa, aceptas estos terminos y la politica de privacidad vigente.',
                    'Si no estas de acuerdo con estas condiciones, debes abstenerte de utilizar la plataforma.',
                ],
            ],
            [
                'heading' => '3. Requisitos de cuenta y uso permitido',
                'body' => [
                    'Quien publique o gestione anuncios debe proporcionar informacion veraz, actualizada y suficiente para identificar el vehiculo y al anunciante.',
                    'No se permite publicar contenido falso, enganoso, duplicado, ofensivo, contrario a la ley, ni anuncios de vehiculos sin autorizacion para su venta.',
                    'El usuario es responsable de custodiar sus credenciales y de toda actividad realizada desde su cuenta.',
                ],
            ],
            [
                'heading' => '4. Publicaciones y responsabilidad del anunciante',
                'body' => [
                    'Cada anunciante es el unico responsable del precio, kilometraje, estado mecanico, gravamenes, marchamo, revision tecnica, disponibilidad, fotografias y demas datos de su vehiculo.',
                    'El anunciante garantiza que tiene derecho para ofrecer el vehiculo y que la informacion publicada no infringe derechos de terceros.',
                    'Movikaa puede editar aspectos de formato, pausar, rechazar o retirar anuncios que incumplan estas condiciones o que generen riesgo para usuarios o para la plataforma.',
                ],
            ],
            [
                'heading' => '5. Contacto entre compradores y vendedores',
                'body' => [
                    'Movikaa puede habilitar formularios, mensajeria, telefono, WhatsApp u otros medios para facilitar el contacto entre las partes.',
                    'Las negociaciones, revisiones, pruebas de manejo, pagos, traspasos, financiamientos, garantias y entrega del vehiculo son responsabilidad exclusiva de comprador y vendedor.',
                    'Recomendamos verificar identidad, documentos, historial del vehiculo y estado registral antes de cerrar cualquier negocio.',
                ],
            ],
            [
                'heading' => '6. Pagos, planes y servicios adicionales',
                'body' => [
                    'Algunas funciones pueden estar sujetas a cobro, suscripcion o pago puntual, incluyendo publicaciones destacadas, planes comerciales, herramientas premium o servicios de apoyo.',
                    'Los precios, alcances, vigencias y condiciones comerciales se informaran dentro de la plataforma o en la propuesta comercial aplicable.',
                    'Salvo indicacion distinta o exigencia legal, los pagos por servicios digitales ya prestados no son reembolsables.',
                ],
            ],
            [
                'heading' => '7. Propiedad intelectual',
                'body' => [
                    'El software, la marca Movikaa, sus disenos, bases de datos, textos, logotipos y elementos distintivos pertenecen a sus titulares y estan protegidos por la normativa aplicable.',
                    'El usuario conserva los derechos sobre el contenido que publica, pero otorga a Movikaa una licencia no exclusiva para alojarlo, reproducirlo, adaptarlo en formato y mostrarlo con fines operativos, promocionales y comerciales relacionados con la plataforma.',
                ],
            ],
            [
                'heading' => '8. Prohibiciones',
                'body' => [
                    'Queda prohibido usar la plataforma para fraude, suplantacion, lavado de dinero, captacion no autorizada de datos, envio de spam, scraping abusivo, ataques informaticos o cualquier actividad ilicita.',
                    'Tambien se prohibe interferir con la seguridad, disponibilidad o funcionamiento normal del sitio.',
                ],
            ],
            [
                'heading' => '9. Limitacion de responsabilidad',
                'body' => [
                    'Movikaa procura mantener la informacion y la disponibilidad del sitio en condiciones razonables, pero no garantiza operacion ininterrumpida ni ausencia absoluta de errores.',
                    'En la medida permitida por la ley, Movikaa no responde por danos derivados de negociaciones entre usuarios, informacion suministrada por terceros, fallos de conectividad, uso indebido de cuentas o decisiones de compra y venta tomadas con base en publicaciones.',
                ],
            ],
            [
                'heading' => '10. Suspension y terminacion',
                'body' => [
                    'Movikaa puede suspender o cancelar cuentas, anuncios o accesos cuando detecte incumplimientos, riesgos de seguridad, requerimientos legales o conductas que afecten la confianza de la comunidad.',
                    'La eliminacion de una cuenta no extingue obligaciones pendientes surgidas antes de su cierre.',
                ],
            ],
            [
                'heading' => '11. Modificaciones',
                'body' => [
                    'Estos terminos pueden actualizarse para reflejar cambios legales, operativos o funcionales. La version publicada en esta pagina sera la vigente desde su fecha de actualizacion.',
                ],
            ],
            [
                'heading' => '12. Ley aplicable y jurisdiccion',
                'body' => [
                    'Estos terminos se interpretan conforme a las leyes de la Republica de Costa Rica. Cualquier conflicto se sometera a la jurisdiccion de los tribunales competentes de Costa Rica, salvo disposicion legal imperativa en contrario.',
                ],
            ],
        ]);
    }

    public function privacy(): View
    {
        $pageTitle = 'Politica de privacidad';
        $pageDescription = 'Como Movikaa recopila, usa, conserva y protege datos personales en el contexto de su marketplace automotriz en Costa Rica.';
        $operatorName = $this->legalOperatorName();

        return $this->renderLegalPage('legal.privacy', $pageTitle, $pageDescription, [
            [
                'heading' => 'Datos del responsable',
                'body' => [
                    sprintf('Para efectos de esta politica, el servicio se presta bajo la marca Movikaa y es administrado por %s como responsable del tratamiento de los datos recopilados a traves de la plataforma.', $operatorName),
                    sprintf('Las solicitudes relacionadas con privacidad, datos personales o ejercicio de derechos pueden enviarse a %s.', config('mail.from.address', 'soporte@movikaa.co')),
                ],
            ],
            [
                'heading' => '1. Responsable del tratamiento',
                'body' => [
                    'Movikaa actua como responsable del tratamiento de los datos personales que recopila a traves del sitio, formularios, cuentas de usuario, canales de contacto y servicios relacionados con la plataforma.',
                ],
            ],
            [
                'heading' => '2. Datos que podemos recopilar',
                'body' => [
                    'Podemos recopilar datos de identificacion y contacto, como nombre, correo electronico, telefono y datos de cuenta.',
                    'Tambien podemos recopilar informacion comercial y tecnica vinculada con publicaciones de vehiculos, consultas, historial de uso, direcciones IP, cookies, eventos de navegacion y registros de seguridad.',
                ],
            ],
            [
                'heading' => '3. Finalidades del uso de datos',
                'body' => [
                    'Usamos la informacion para crear y administrar cuentas, publicar anuncios, facilitar el contacto entre usuarios, atender solicitudes, prevenir fraude, mejorar la experiencia del sitio y cumplir obligaciones legales o regulatorias.',
                    'Adicionalmente, podremos usar datos para analitica, soporte, recordatorios operativos, comunicaciones sobre el servicio y acciones comerciales relacionadas con Movikaa, siempre dentro del marco legal aplicable.',
                ],
            ],
            [
                'heading' => '4. Base de uso y consentimiento',
                'body' => [
                    'Tratamos datos cuando son necesarios para ejecutar la relacion con el usuario, prestar servicios solicitados, cumplir obligaciones legales, proteger intereses legitimos de seguridad y operacion, o cuando exista consentimiento del titular.',
                    'Cuando la ley lo requiera, solicitaremos autorizacion previa para ciertos tratamientos o comunicaciones.',
                ],
            ],
            [
                'heading' => '5. Comparticion de informacion',
                'body' => [
                    'Movikaa puede compartir datos con proveedores tecnologicos, alojamiento, mensajeria, analitica, seguridad, pagos y otros encargados que necesiten tratar informacion para operar la plataforma bajo deberes de confidencialidad.',
                    'Asimismo, ciertos datos del anunciante o del vehiculo pueden hacerse visibles a potenciales compradores como parte natural del servicio.',
                    'Tambien podremos divulgar informacion si una autoridad competente lo requiere o cuando sea necesario para prevenir fraude o proteger derechos.',
                ],
            ],
            [
                'heading' => '6. Conservacion',
                'body' => [
                    'Conservamos los datos durante el tiempo necesario para cumplir las finalidades descritas, atender obligaciones legales, resolver disputas, ejercer defensas y mantener trazabilidad operativa y de seguridad.',
                ],
            ],
            [
                'heading' => '7. Seguridad',
                'body' => [
                    'Aplicamos medidas razonables de seguridad administrativa, tecnica y organizativa para proteger la informacion frente a acceso no autorizado, perdida, alteracion o divulgacion indebida.',
                    'Sin embargo, ningun sistema conectado a internet es absolutamente infalible, por lo que no puede garantizarse seguridad total.',
                ],
            ],
            [
                'heading' => '8. Derechos de las personas usuarias',
                'body' => [
                    'El titular de los datos puede solicitar acceso, rectificacion, actualizacion o eliminacion de su informacion, asi como ejercer los derechos que reconozca la legislacion costarricense aplicable.',
                    'Tambien puede solicitar la baja de comunicaciones promocionales cuando corresponda.',
                ],
            ],
            [
                'heading' => '9. Datos de menores de edad',
                'body' => [
                    'La plataforma no esta dirigida a menores de edad para operaciones de publicacion o compraventa. Si detectamos tratamiento no autorizado de datos de una persona menor de edad, podremos eliminar o restringir esa informacion.',
                ],
            ],
            [
                'heading' => '10. Transferencias y terceros',
                'body' => [
                    'Algunos proveedores de infraestructura o soporte pueden procesar informacion fuera de Costa Rica. En esos casos procuramos que existan medidas contractuales y operativas razonables para la proteccion de los datos.',
                ],
            ],
            [
                'heading' => '11. Cambios a esta politica',
                'body' => [
                    'Movikaa puede actualizar esta politica para reflejar cambios normativos, tecnicos o del servicio. La version publicada en esta pagina sera la vigente desde su actualizacion.',
                ],
            ],
        ]);
    }

    public function cookies(): View
    {
        $pageTitle = 'Politica de cookies';
        $pageDescription = 'Explicacion del uso de cookies y tecnologias similares dentro de Movikaa para funciones esenciales, rendimiento y analitica.';

        return $this->renderLegalPage('legal.cookies', $pageTitle, $pageDescription, [
            [
                'heading' => 'Datos de contacto',
                'body' => [
                    sprintf('Si tienes preguntas sobre el uso de cookies o sobre la configuracion del sitio, puedes escribir a %s.', config('mail.from.address', 'soporte@movikaa.co')),
                ],
            ],
            [
                'heading' => '1. Que son las cookies',
                'body' => [
                    'Las cookies son pequenos archivos de texto que un sitio web guarda en el navegador o dispositivo del usuario para recordar informacion sobre su visita.',
                ],
            ],
            [
                'heading' => '2. Para que usamos cookies',
                'body' => [
                    'Movikaa utiliza cookies y tecnologias similares para mantener sesiones activas, recordar preferencias, reforzar medidas de seguridad, entender como se usa la plataforma y mejorar el rendimiento del sitio.',
                ],
            ],
            [
                'heading' => '3. Tipos de cookies que podemos usar',
                'body' => [
                    'Cookies estrictamente necesarias: permiten funciones esenciales como autenticacion, seguridad, balance de carga y navegacion basica.',
                    'Cookies funcionales: recuerdan preferencias de idioma, sesion, filtros u otras opciones del usuario.',
                    'Cookies de analitica o rendimiento: ayudan a medir trafico, detectar errores y entender el comportamiento de navegacion para mejorar la experiencia.',
                    'Cookies de terceros: pueden establecerse por herramientas externas integradas en el sitio, como servicios de medicion, mapas, mensajeria o soporte.',
                ],
            ],
            [
                'heading' => '4. Duracion',
                'body' => [
                    'Algunas cookies se eliminan al cerrar el navegador y otras pueden permanecer durante un periodo determinado para recordar configuraciones o facilitar futuras visitas.',
                ],
            ],
            [
                'heading' => '5. Gestion de preferencias',
                'body' => [
                    'Puedes configurar tu navegador para bloquear o eliminar cookies. Sin embargo, al deshabilitar cookies esenciales, ciertas funciones del sitio podrian dejar de operar correctamente.',
                ],
            ],
            [
                'heading' => '6. Relacion con datos personales',
                'body' => [
                    'Cuando una cookie se vincula con una persona identificada o identificable, su tratamiento se rige tambien por nuestra Politica de Privacidad.',
                ],
            ],
            [
                'heading' => '7. Actualizaciones',
                'body' => [
                    'Esta politica puede modificarse para reflejar cambios tecnologicos, regulatorios o funcionales dentro de Movikaa.',
                ],
            ],
        ]);
    }

    private function renderLegalPage(string $routeName, string $pageTitle, string $pageDescription, array $pageSections): View
    {
        return view('legal.page', [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageSections' => $pageSections,
            'lastUpdated' => '29 de abril de 2026',
            'contactEmail' => config('mail.from.address', 'soporte@movikaa.co'),
            'seoData' => $this->seoService->forLegal($pageTitle, $pageDescription, route($routeName), request()),
        ]);
    }

    private function legalOperatorName(): string
    {
        return (string) config('mail.from.name', config('app.name', 'Movikaa'));
    }
}
