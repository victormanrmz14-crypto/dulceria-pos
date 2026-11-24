<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\SuperAdmin\Analytics;
use App\Services\SuperAdmin\HealthChecks;
use Illuminate\Database\QueryException;
use Inertia\Inertia;

class MonitoreoController extends Controller
{
    public function index(Analytics $analytics, HealthChecks $checks)
    {
        $snapshot = $checks->snapshot();
        try {
            $tenants = $analytics->tenants()->orderBy('nombre')->orderBy('id')->paginate(20)->withQueryString()->through(fn ($t) => $analytics->row($t));
        } catch (QueryException) {
            $tenants = null;
        }

        return Inertia::render('SuperAdmin/Monitoreo', [...$snapshot, 'tenants' => $tenants, 'periodo' => $analytics->period()]);
    }
}
