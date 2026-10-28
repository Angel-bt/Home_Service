<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function autoComplete(Request $req)
    {
        $data = Service::query()
            ->active()
            ->select('name')
            ->search((string) $req->input('query'))
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    // Formularios antiguos que hacen POST: se redirigen a la búsqueda real (GET).
    public function searchService(Request $req)
    {
        return redirect()->route('search', array_filter(['q' => trim((string) $req->input('q'))]));
    }
}
