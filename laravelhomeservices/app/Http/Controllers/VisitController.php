<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit; // Asegúrate de importar el modelo Visit

class VisitController extends Controller
{
    // Método para incrementar el contador de visitas
    public function incrementVisit(Request $request)
    {
        // Verificar si ya se ha registrado una visita en esta sesión
        if (!$request->session()->has('visit_counted')) {

            // Obtener el nombre del usuario autenticado
            $user = auth()->user();
            $visitorName = $user ? $user->name : 'Invitado';

            // MEJORA BUG: conservar un único registro global para el contador.
            $visit = Visit::firstOrCreate(
                ['id' => 1],
                ['visitor_name' => $visitorName]
            );


            // Incrementar el contador de visitas
            $visit->increment('visits');

            // Marcar la sesión como "visit_counted"
            $request->session()->put('visit_counted', true);

            // Devolver el contador actualizado
            return $visit->visits;
        }

        // Si ya se ha registrado una visita en esta sesión, devolver el contador actual
        $visit = Visit::firstOrCreate(['id' => 1]);
        return $visit->visits;
    }

    // Método para obtener todas las visitas
    public function getVisits()
    {
        // Obtener todos los registros de visitas
        $visits = Visit::orderBy('created_at', 'desc')->get();

        // Devolver las visitas en formato JSON
        return response()->json(['success' => true, 'visits' => $visits]);
    }

    

    // Método para reiniciar el contador de visitas
    public function resetVisits(Request $request)
    {
        // Obtener el primer registro (o crearlo si no existe)
        $visit = Visit::firstOrCreate(['id' => 1]);

        // Reiniciar el contador de visitas a 0
        $visit->update(['visits' => 0]);

        // Limpiar la sesión de "visit_counted" para permitir un nuevo incremento
        $request->session()->forget('visit_counted');

        // Devolver una respuesta JSON indicando éxito
        return response()->json(['success' => true, 'visits' => $visit->visits]);
    }
}
