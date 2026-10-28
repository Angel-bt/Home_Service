<?php

namespace App\Http\Livewire\Admin;

use Livewire\Component;
use App\Models\User;   // Modelo de usuarios
use App\Models\Service; // Modelo de servicios
use App\Models\ServiceCategory;// Modelo de categorías de servicios
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use App\Models\Visit; // Importar el modelo Visit

class AdminDashboardComponent extends Component
{
    public function render()
    {
        try {
            $totalUsers = User::count();
            $totalServices = Service::count();
            $totalServiceCategory = ServiceCategory::count();
            $totalContacts = Contact::count(); // En lugar de `pendingRequests`
            // Obtener el contador de visitas
            $visitCount = Visit::counter()->visits;

            // Obtener todas las visitas ordenadas por fecha de creación
            $visits = Visit::orderBy('created_at', 'desc')->get();

            // Obtener las categorías de servicios con el número de servicios asociados
            $serviceCategories = ServiceCategory::withCount('services')->orderBy('services_count', 'desc')->take(5)->get();
        } catch (\Exception $e) {
            report($e);
            session()->flash('error', 'Error loading dashboard data.');
            $totalUsers = $totalServices = $totalServiceCategory = $totalContacts = $visitCount = 0;
            $visits = collect();
            $serviceCategories = collect(); // Inicializar una colección vacía en caso de error
        }

        return view('livewire.admin.admin-dashboard-component', [
            'totalUsers' => $totalUsers,
            'totalServices' => $totalServices,
            'totalServiceCategory' => $totalServiceCategory,
            'totalContacts' => $totalContacts, // Reemplazando `pendingRequests`
            'visitCount' => $visitCount, // Contador global de visitas
            'visits' => $visits, // Registros de visitas para la tabla
            'serviceCategories' => $serviceCategories, // Pasar las categorías de servicios a la vista
        ])->layout('layouts.base');
    }
}
