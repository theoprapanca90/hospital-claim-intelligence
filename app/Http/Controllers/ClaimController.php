<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Models\Claim;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ClaimController
{
    public function __construct(private readonly AuditLogger $audit)
    {
    }

    public function index(Request $request)
    {
        $hospital = $request->attributes->get('hospital');
        Gate::authorize('viewAny', [Claim::class, (string) $hospital->id]);

        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));

        $claims = Claim::query()
            ->forHospital((string) $hospital->id)
            ->orderByDesc('created_at')
            ->orderBy('id')
            ->simplePaginate($perPage);

        $this->audit->record('claim.listed', (string) $hospital->id, $request->user()->getKey(), null, [
            'count' => $claims->count(),
        ]);

        return ClaimResource::collection($claims);
    }

    public function show(Request $request)
    {
        $hospital = $request->attributes->get('hospital');

        $claim = Claim::query()
            ->forHospital((string) $hospital->id)
            ->findOrFail($request->route('claim'));

        Gate::authorize('view', $claim);

        $this->audit->record('claim.viewed', (string) $hospital->id, $request->user()->getKey(), $claim);

        return new ClaimResource($claim);
    }
}
