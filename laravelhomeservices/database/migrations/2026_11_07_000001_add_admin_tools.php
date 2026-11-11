<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Herramientas para que la administración maneje el sitio sin tocar código: ajustes, suspensión
 * de cuentas, reseñas ocultas, mensajes respondidos y preguntas frecuentes editables.
 * Solo agrega columnas opcionales y tablas nuevas: se puede correr antes de desplegar el código.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->string('key', 100)->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
        });

        // status = la administración lo muestra u oculta; paused_at = el proveedor lo pausa (vacaciones).
        Schema::table('services', function (Blueprint $table): void {
            $table->timestamp('paused_at')->nullable();
        });

        Schema::table('reviews', function (Blueprint $table): void {
            $table->timestamp('hidden_at')->nullable();
            $table->string('hidden_reason')->nullable();
        });

        Schema::table('contacts', function (Blueprint $table): void {
            $table->timestamp('answered_at')->nullable();
        });

        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->string('group', 40);
            $table->string('question');
            $table->text('answer');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        // Las preguntas que estaban escritas en el código, para no empezar con la página vacía.
        $now = now();
        $position = 0;
        $rows = [];
        foreach ($this->defaults() as $group => $items) {
            foreach ($items as $question => $answer) {
                $rows[] = ['group' => $group, 'question' => $question, 'answer' => $answer, 'position' => ++$position, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('faqs')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
        Schema::table('contacts', fn (Blueprint $table) => $table->dropColumn('answered_at'));
        Schema::table('reviews', fn (Blueprint $table) => $table->dropColumn(['hidden_at', 'hidden_reason']));
        Schema::table('services', fn (Blueprint $table) => $table->dropColumn('paused_at'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['suspended_at', 'suspension_reason']));
        Schema::dropIfExists('settings');
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function defaults(): array
    {
        return [
            'Para clientes' => [
                '¿Cuánto cuesta usar Malakplace?' => 'Para los clientes es gratis. Buscas, contactas y solicitas servicios sin pagar nada a la plataforma.',
                '¿Cómo contacto a un proveedor?' => 'Entra al servicio que te interesa. Ahí tienes un botón verde de WhatsApp para escribirle de inmediato y un formulario para enviarle una solicitud con tu zona, dirección y fecha.',
                '¿Cómo pago el servicio?' => 'Le pagas directamente al proveedor (efectivo o transferencia), según lo que acuerden. El precio publicado es de referencia; el final depende del trabajo.',
                '¿Dónde veo mis solicitudes?' => 'Ingresa con tu cuenta y toca «Mis solicitudes» arriba a la derecha. Ahí ves si el proveedor ya aceptó, puedes cancelar y, al terminar, calificar.',
                '¿Malakplace responde si algo sale mal?' => 'Malakplace solo conecta a las personas: no hace los trabajos, no contrata a los proveedores y no recibe el pago, así que no responde por robos, daños, trabajos mal hechos ni si alguien no llega. Si pasa algo grave, avísanos para revisar esa cuenta y acude a las autoridades.',
                '¿Qué significan los sellos?' => 'Son voluntarios. «Identidad verificada» y «Sin antecedentes penales» dicen que revisamos ese documento en la fecha que aparece. Ayudan a decidir, pero no son una garantía. Que alguien no tenga un sello no significa nada malo.',
                '¿Quién puede dejar reseñas?' => 'Solo los clientes cuya solicitud el proveedor marcó como terminada. Así las reseñas son de trabajos reales.',
            ],
            'Para proveedores' => [
                '¿Cómo publico mis servicios?' => 'Crea una cuenta eligiendo «Ofrezco servicios». En tu panel verás los pasos: completar tu perfil (WhatsApp y zonas) y publicar tu primer servicio. El plan gratuito permite {limite_gratis} servicios.',
                '¿Cómo me llegan los clientes?' => 'Te escriben a tu WhatsApp o te envían una solicitud. Las solicitudes aparecen en tu panel, en «Solicitudes», con un número que te avisa cuántas faltan por responder.',
                '¿Tengo que entregar mis documentos?' => 'No. Es voluntario: puedes publicar y trabajar sin sellos. Si los entregas, solo los ve la administración, se borran apenas los revisamos y en tu perfil solo aparece el sello con su fecha. Puedes retirarlo cuando quieras desde «Verificarme».',
                '¿Qué es el plan Pro?' => 'Te permite publicar servicios sin límite por {precio_pro} cada {dias_pro} días. Se paga por transferencia o en efectivo desde «Mi plan», y lo activamos cuando confirmamos el pago.',
            ],
            'General' => [
                '¿En qué zonas funciona?' => 'En {zonas}.',
                '¿Qué hago si tengo un problema?' => 'Escríbenos por WhatsApp o desde la página de contacto con los detalles y lo revisamos.',
            ],
        ];
    }
};
