<?php

declare(strict_types=1);

/*
 * Textos del sitio. Son el valor inicial: la administración los cambia en «Textos y aviso» y
 * «Textos legales» (App\Support\SiteSettings guarda lo que cambia en la tabla `settings`).
 * Lo que va entre *asteriscos* se muestra destacado (App\Support\Content::rich).
 */

$legal = static fn (string $file): string => (string) @file_get_contents(dirname(__DIR__) . '/resources/legal/' . $file);

return [
    'hero_eyebrow' => 'Malacatos — Loja, Ecuador',
    'hero_title' => 'Gente de oficio cerca de ti, *en Malacatos.*',
    'hero_lede' => 'Gasfiteros, electricistas, jardineros y más de Malacatos, Vilcabamba, Yangana y Quinara. Les escribes directo y les pagas a ellos.',
    'about_text' => 'Reunimos a la gente de oficio de Malacatos y sus parroquias vecinas. Encuentras a quien te queda cerca, lees lo que dicen otros vecinos y le escribes *directo.*',
    'providers_title' => '¿Trabajas en Malacatos? *Que te encuentren.*',
    'footer_tagline' => 'Atendemos desde Malacatos. Respondemos el mismo día.',

    'seo_title' => 'Malakplace · Gente de oficio en Malacatos',
    'seo_description' => 'Gasfiteros, electricistas, jardineros y más en Malacatos, Vilcabamba, Yangana y Quinara. Mira sus reseñas y escríbeles directo por WhatsApp.',
    'share_image' => null,

    'announcement' => [
        'enabled' => false,
        'text' => null,
        'link' => null,
    ],

    'legal' => [
        'terms' => $legal('terminos.md'),
        'privacy' => $legal('privacidad.md'),
        'summary_all' => $legal('resumen-todos.md'),
        'summary_provider' => $legal('resumen-proveedores.md'),
        'summary_customer' => $legal('resumen-clientes.md'),
        'consent' => trim($legal('permiso-publicacion.txt')),
    ],
];
