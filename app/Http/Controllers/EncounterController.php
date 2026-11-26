<?php

namespace App\Http\Controllers;

use App\Http\Resources\EncounterResource;
use App\Models\Encounter;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EncounterController
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $hospital = $request->attributes->get('hospital');
        Gate::authorize('viewAny', [Encounter::class, (string) $hospital->id]);

        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));

        $encounters = Encounter::query()
            ->forHospital((string) $hospital->id)
            ->orderByDesc('started_at')
            ->orderBy('id')
            ->simplePaginate($perPage);

        $this->audit->record('encounter.listed', (string) $hospital->id, $request->user()->getKey(), null, [
            'count' => $encounters->count(),
        ]);

        return EncounterResource::collection($encounters);
    }

    public function show(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        // Scope tenant diterapkan di query; encounter RS lain tidak pernah ter-load.
        $encounter = Encounter::query()
            ->forHospital((string) $hospital->id)
            ->findOrFail($request->route('encounter'));

        Gate::authorize('view', $encounter);

        $this->audit->record('encounter.viewed', (string) $hospital->id, $request->user()->getKey(), $encounter);

        return new EncounterResource($encounter);
    }
}
