<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Response;

/**
 * Lista de páginas públicas para Google y otros buscadores (la anuncia public/robots.txt).
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'lastmod' => null],
            ['loc' => route('home.service_categories'), 'lastmod' => null],
            ['loc' => route('search'), 'lastmod' => null],
            ['loc' => route('home.faq'), 'lastmod' => null],
            ['loc' => route('home.about'), 'lastmod' => null],
            ['loc' => route('home.contact'), 'lastmod' => null],
        ];

        foreach (ServiceCategory::query()->orderBy('name')->get(['slug', 'updated_at']) as $category) {
            $urls[] = ['loc' => route('home.services_by_category', ['category_slug' => $category->slug]), 'lastmod' => $category->updated_at];
        }

        foreach (Service::query()->active()->orderBy('id')->get(['slug', 'updated_at']) as $service) {
            $urls[] = ['loc' => route('home.service_details', ['service_slug' => $service->slug]), 'lastmod' => $service->updated_at];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $url) {
            $xml .= '  <url><loc>' . e($url['loc']) . '</loc>' . ($url['lastmod'] ? '<lastmod>' . $url['lastmod']->toAtomString() . '</lastmod>' : '') . "</url>\n";
        }
        $xml .= "</urlset>\n";

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8', 'Cache-Control' => 'public, max-age=3600']);
    }
}
