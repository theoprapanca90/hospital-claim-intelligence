<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/v1/me', function (Request $request) {
        return response()->json([
            'data' => [
                'id' => (string) $request->user()->getAuthIdentifier(),
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
        ]);
    });

    Route::middleware('hospital.member')
        ->prefix('/v1/hospitals/{hospital}')
        ->group(function (): void {
            Route::get('/context', function (Request $request) {
                $membership = $request->attributes->get('hospital_membership');
                $hospital = $request->attributes->get('hospital');

                return response()->json([
                    'data' => [
                        'hospital' => [
                            'id' => (string) $hospital->id,
                            'code' => $hospital->code,
                            'name' => $hospital->name,
                        ],
                        'membership' => [
                            'id' => $membership->id,
                            'status' => $membership->status,
                            'roles' => $membership->effectiveRoles()->pluck('slug')->values(),
                        ],
                    ],
                ]);
            });
        });
});
