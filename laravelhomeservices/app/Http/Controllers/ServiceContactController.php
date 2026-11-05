<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityEvent;
use App\Models\Service;
use App\Support\Activity;
use Illuminate\Http\RedirectResponse;

class ServiceContactController extends Controller
{
    /**
     * El botón de WhatsApp pasa por aquí para contar el contacto y sigue directo a WhatsApp.
     */
    public function whatsapp(string $service_slug): RedirectResponse
    {
        $service = Service::query()->with('user.serviceProvider')->where('slug', $service_slug)->firstOrFail();
        $link = $service->whatsappLink();

        abort_if($link === null, 404);

        Activity::forService(ActivityEvent::WHATSAPP_CLICK, $service);

        return new RedirectResponse($link);
    }
}
