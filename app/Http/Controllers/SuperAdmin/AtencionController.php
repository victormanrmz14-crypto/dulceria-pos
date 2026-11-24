<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\Analytics;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AtencionController extends Controller
{
    public function index(Request $request, Analytics $analytics): Response
    {
        $filters = $request->validate(['tipo' => ['nullable', Rule::in(array_column($analytics->criteria(), 'tipo'))]]);
        try {
            $tenants = $analytics->attention($analytics->tenants(), $filters['tipo'] ?? null)
                ->orderBy('tenants.created_at')->orderBy('tenants.id')->paginate(20)->withQueryString()
                ->through(fn ($t) => $analytics->row($t));
        } catch (QueryException $exception) {
            report($exception);
            $tenants = null;
        }

        return Inertia::render('SuperAdmin/Atencion', [
            'tenants' => $tenants, 'filters' => $filters, 'criterios' => $analytics->criteria(), 'periodo' => $analytics->period(),
        ]);
    }
}
