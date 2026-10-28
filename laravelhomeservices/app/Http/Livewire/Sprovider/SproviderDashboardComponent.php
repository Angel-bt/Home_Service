<?php

declare(strict_types=1);

namespace App\Http\Livewire\Sprovider;

use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SproviderDashboardComponent extends Component
{
    public function render()
    {
        $user = Auth::user();
        $profile = $user->serviceProvider()->withCount('zones')->first();
        $requests = ServiceRequest::where('provider_id', $user->id);

        return view('livewire.sprovider.sprovider-dashboard-component', [
            'servicesCount' => $user->services()->count(),
            'pendingCount' => (clone $requests)->where('status', ServiceRequest::PENDING)->count(),
            'completedCount' => (clone $requests)->where('status', ServiceRequest::COMPLETED)->count(),
            'rating' => Review::whereIn('service_id', $user->services()->select('id'))->avg('rating'),
            'profileIncomplete' => $profile === null || $profile->zones_count === 0 || ! $profile->whatsapp,
            'limit' => $user->servicePublicationLimit(),
            'latestRequests' => (clone $requests)->with('service:id,name,slug')->latest()->take(5)->get(),
        ])->layout('layouts.base');
    }
}
